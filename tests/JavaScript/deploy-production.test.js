import assert from "node:assert/strict";
import { mkdirSync, mkdtempSync, readFileSync, readdirSync, rmSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join, resolve } from "node:path";
import { spawnSync } from "node:child_process";
import test from "node:test";

const driver = resolve(".github/scripts/deploy-production.sh");
const configureSsh = resolve(".github/scripts/configure-deploy-ssh.sh");

function execute(command, args, options = {}) {
    const result = spawnSync(command, args, { encoding: "utf8", ...options });
    assert.equal(result.error, undefined);
    assert.equal(result.status, 0, result.stderr + result.stdout);
    return result.stdout.trim();
}

function withRepository(callback) {
    const directory = mkdtempSync(join(tmpdir(), "dlf-deploy-test-"));
    try {
        const origin = join(directory, "origin.git");
        const author = join(directory, "author");
        const runner = join(directory, "runner");
        const bin = join(directory, "bin");
        mkdirSync(bin);
        mkdirSync(join(directory, "temporary"));
        const git = (...args) => execute("git", args);
        git("init", "--bare", "--initial-branch=main", origin);
        git("clone", origin, author);
        git("-C", author, "config", "user.name", "Deployment test");
        git("-C", author, "config", "user.email", "deploy@example.invalid");
        writeFileSync(join(author, "source.txt"), "initial content\n");
        git("-C", author, "add", ".");
        git("-C", author, "commit", "-m", "initial");
        git("-C", author, "push", "origin", "main");
        git("clone", origin, runner);
        const initial = git("-C", runner, "rev-parse", "HEAD");

        // This double never connects to a server. It advances a local bare remote.
        writeFileSync(join(bin, "composer"), `#!/usr/bin/env bash
set -euo pipefail
if [[ "$1" == install ]]; then
    printf '%s\n' "$(git rev-parse HEAD)" >> "$FIXTURE/installs"
    if [[ "$MODE" == install-failure ]]; then
        echo DLF_DEPLOY_RETRY_MAIN_ADVANCED
        exit 75
    fi
    exit 0
fi
count=0
[[ ! -f "$FIXTURE/count" ]] || count=$(cat "$FIXTURE/count")
count=$((count + 1))
printf '%s' "$count" > "$FIXTURE/count"
printf '%s\n' "$*" >> "$FIXTURE/deploys"
case "$MODE" in
    stale-first|stale-always)
        if [[ "$count" == 1 || "$MODE" == stale-always ]]; then
            git -C "$AUTHOR" commit --allow-empty -m "content $count" > /dev/null
            git -C "$AUTHOR" push origin main > /dev/null 2>&1
            echo '[dlf-production]: DLF_DEPLOY_RETRY_MAIN_ADVANCED'
            exit 75
        fi
        ;;
    failure) exit 42 ;;
    marker-only) echo DLF_DEPLOY_RETRY_MAIN_ADVANCED; exit 42 ;;
    status-only) exit 75 ;;
esac
`, { mode: 0o755 });

        const run = (mode, extra = {}) => {
            const result = spawnSync("bash", [driver], {
                cwd: runner,
                encoding: "utf8",
                timeout: 20000,
                env: {
                    ...process.env,
                    PATH: `${bin}:${process.env.PATH}`,
                    TMPDIR: join(directory, "temporary"),
                    FIXTURE: directory,
                    AUTHOR: author,
                    MODE: mode,
                    DEPLOY_SERVER: "dlf-production",
                    DEPLOY_PATH: "/not-a-real-production-directory",
                    DEPLOY_RETRY_DELAY_SECONDS: "0",
                    REQUIRED_REVISION: initial,
                    ...extra,
                },
            });
            assert.equal(result.error, undefined);
            assert.deepEqual(readdirSync(join(directory, "temporary")), [], "temporary checkouts and logs are removed");
            return result;
        };
        callback({ directory, author, runner, initial, git, run });
    } finally {
        rmSync(directory, { recursive: true, force: true });
    }
}

test("deploy includes content pushed after the triggering PR and retries fresh main", () => {
    withRepository(({ directory, author, initial, git, run }) => {
        git("-C", author, "commit", "--allow-empty", "-m", "content before job starts");
        git("-C", author, "push", "origin", "main");
        const beforeJob = git("-C", author, "rev-parse", "HEAD");
        const result = run("stale-first");
        assert.equal(result.status, 0, result.stderr);
        const attempts = readFileSync(join(directory, "installs"), "utf8").trim().split("\n");
        assert.equal(attempts.length, 2);
        assert.notEqual(beforeJob, initial);
        assert.equal(attempts[0], beforeJob);
        assert.equal(attempts[1], git("-C", author, "rev-parse", "HEAD"));
        assert.notEqual(attempts[0], attempts[1]);
        const deploys = readFileSync(join(directory, "deploys"), "utf8");
        for (const revision of attempts) assert.ok(deploys.includes(`--revision=${revision}`));
    });
});

test("deploy stops after three stale attempts", () => {
    withRepository(({ directory, run }) => {
        const result = run("stale-always");
        assert.equal(result.status, 75, result.stderr);
        assert.equal(readFileSync(join(directory, "count"), "utf8"), "3");
        assert.match(result.stderr, /after three attempts/);
    });
});

for (const [mode, status] of [["failure", 42], ["marker-only", 42], ["status-only", 75]]) {
    test(`deploy does not retry ${mode}`, () => {
        withRepository(({ directory, run }) => {
            const result = run(mode);
            assert.equal(result.status, status, result.stderr);
            assert.equal(readFileSync(join(directory, "count"), "utf8"), "1");
        });
    });
}

test("dependency installation failure never starts deployment or retries", () => {
    withRepository(({ directory, run }) => {
        const result = run("install-failure");
        assert.equal(result.status, 75);
        assert.equal(readdirSync(directory).includes("count"), false);
        assert.equal(readFileSync(join(directory, "installs"), "utf8").trim().split("\n").length, 1);
    });
});

test("deploy rejects main rewritten to exclude the merged PR", () => {
    withRepository(({ directory, author, git, run }) => {
        git("-C", author, "checkout", "--orphan", "replacement");
        git("-C", author, "commit", "-m", "different history");
        git("-C", author, "push", "--force", "origin", "HEAD:main");
        const result = run("success");
        assert.equal(result.status, 1);
        assert.match(result.stderr, /does not contain the merged pull request/);
        assert.equal(readdirSync(directory).includes("installs"), false);
    });
});

test("SSH setup pins the host and rejects configuration injection without connecting", () => {
    const directory = mkdtempSync(join(tmpdir(), "dlf-ssh-test-"));
    try {
        const key = join(directory, "key");
        execute("ssh-keygen", ["-q", "-t", "ed25519", "-N", "", "-f", key]);
        const env = {
            ...process.env,
            HOME: directory,
            DEPLOY_HOST: "example.invalid",
            DEPLOY_USER: "deploy",
            DEPLOY_PORT: "2222",
            DEPLOY_SSH_PRIVATE_KEY: readFileSync(key, "utf8"),
            DEPLOY_SSH_KNOWN_HOSTS: `[example.invalid]:2222 ${readFileSync(`${key}.pub`, "utf8")}`,
        };
        execute("bash", [configureSsh], { env });
        const config = execute("ssh", ["-F", join(directory, ".ssh/config"), "-G", "dlf-production"]);
        assert.match(config, /hostname example\.invalid/);
        assert.match(config, /user deploy/);
        assert.match(config, /port 2222/);
        assert.match(config, /stricthostkeychecking true/);
        assert.match(config, /forwardagent no/);

        for (const extra of [{ DEPLOY_HOST: "host\nProxyCommand bad" }, { DEPLOY_PORT: "0" }, { DEPLOY_USER: "" }]) {
            const result = spawnSync("bash", [configureSsh], { encoding: "utf8", env: { ...env, ...extra } });
            assert.equal(result.status, 1);
            assert.equal(result.stdout.includes(env.DEPLOY_SSH_PRIVATE_KEY), false);
            assert.equal(result.stderr.includes(env.DEPLOY_SSH_PRIVATE_KEY), false);
        }
    } finally {
        rmSync(directory, { recursive: true, force: true });
    }
});
