import assert from "node:assert/strict";
import { existsSync, mkdtempSync, writeFileSync, readFileSync, rmSync } from "node:fs";
import { tmpdir } from "node:os";
import { join, resolve } from "node:path";
import { spawnSync } from "node:child_process";
import test from "node:test";

for (const version of [[3, 2], [4, 2]]) {
    test(`development launcher rejects Bash ${version.join(".")} before invoking php or bun`, () => {
        const directory = mkdtempSync(join(tmpdir(), "dlf-launcher-"));
        try {
            for (const command of ["php", "bun"]) {
                writeFileSync(join(directory, command), `#!/bin/sh
printf invoked > "$FIXTURE/${command}.invoked"
`, { mode: 0o755 });
            }
            // Bash's real version array is readonly. Substitute only the version
            // input in a disposable copy; execute the launcher's unchanged logic.
            const launcher = join(directory, "dev.sh");
            writeFileSync(launcher, `FIXTURE_BASH_VERSINFO=(${version.join(" ")})\n${readFileSync(resolve("dev.sh"), "utf8").replaceAll("BASH_VERSINFO", "FIXTURE_BASH_VERSINFO")}`);

            const result = spawnSync("bash", [launcher], {
                env: {
                    ...process.env,
                    PATH: `${directory}:${process.env.PATH}`,
                    FIXTURE: directory,
                    APP_URL: "http://assigned.example.test",
                    APP_PORT: "12345",
                    VITE_PORT: "12346",
                    INERTIA_SSR_URL: "http://127.0.0.1:12347",
                },
                timeout: 5000,
                encoding: "utf8",
            });

            assert.equal(result.error, undefined);
            assert.equal(result.status, 1, result.stderr);
            assert.match(result.stderr, /requires Bash 4\.3\+/);
            assert.match(result.stderr, /PATH/);
            for (const command of ["php", "bun"]) {
                assert.equal(existsSync(join(directory, `${command}.invoked`)), false, `${command} must not be invoked`);
            }
        } finally {
            rmSync(directory, { recursive: true, force: true });
        }
    });
}

for (const [failed, sibling] of [["php", "bun"], ["bun", "php"]]) {
    test(`development launcher propagates ${failed} failure and reaps ${sibling}`, () => {
        const directory = mkdtempSync(join(tmpdir(), "dlf-launcher-"));
        try {
            // Only disposable command doubles run: no PHP server or Vite process.
            for (const command of ["php", "bun"]) {
                writeFileSync(join(directory, command), `#!/usr/bin/env bash
set -eu
printf '%s' "$$" > "$FIXTURE/${command}.pid"
if [[ "${command}" == "$FAILED" ]]; then
    while [[ ! -f "$FIXTURE/${sibling}.pid" ]]; do sleep 0.01; done
    exit 37
fi
trap 'printf stopped > "$FIXTURE/${command}.stopped"; exit 0' TERM
while true; do sleep 0.05; done
`, { mode: 0o755 });
            }
            const composer = JSON.parse(readFileSync(resolve("composer.json"), "utf8"));
            assert.equal(composer.scripts.dev[1], "bash dev.sh");
            const result = spawnSync("bash", [resolve("dev.sh")], {
                env: {
                    ...process.env,
                    PATH: `${directory}:${process.env.PATH}`,
                    FIXTURE: directory,
                    FAILED: failed,
                    APP_URL: "http://assigned.example.test",
                    APP_PORT: "12345",
                    VITE_PORT: "12346",
                    INERTIA_SSR_URL: "http://127.0.0.1:12347",
                },
                timeout: 5000,
                encoding: "utf8",
            });
            assert.equal(result.error, undefined);
            assert.equal(result.status, 37, result.stderr);
            assert.equal(readFileSync(join(directory, `${sibling}.stopped`), "utf8"), "stopped");
            for (const command of [failed, sibling]) {
                const pid = Number(readFileSync(join(directory, `${command}.pid`), "utf8"));
                assert.throws(() => process.kill(pid, 0), { code: "ESRCH" });
            }
        } finally {
            rmSync(directory, { recursive: true, force: true });
        }
    });
}
