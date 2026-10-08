import assert from "node:assert/strict";
import { existsSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join, resolve } from "node:path";
import { spawnSync } from "node:child_process";
import test from "node:test";

// Runs dev.sh with disposable php/bun doubles on PATH. No real server starts.
function runLauncher(directory, env, doubles) {
    for (const [command, script] of Object.entries(doubles)) {
        writeFileSync(join(directory, command), script, { mode: 0o755 });
    }

    const { APP_PORT, VITE_PORT, INERTIA_SSR_URL, ...inherited } = process.env;

    return spawnSync("bash", [resolve("dev.sh")], {
        env: { ...inherited, PATH: `${directory}:${process.env.PATH}`, FIXTURE: directory, ...env },
        timeout: 10000,
        encoding: "utf8",
    });
}

test("development launcher requires its ports before starting anything", () => {
    const directory = mkdtempSync(join(tmpdir(), "dlf-launcher-"));
    try {
        const marker = `#!/bin/sh\ntouch "$FIXTURE/$(basename "$0").invoked"\n`;
        const result = runLauncher(directory, { APP_PORT: "12345" }, { php: marker, bun: marker });

        assert.notEqual(result.status, 0);
        assert.match(result.stderr, /VITE_PORT/);
        assert.equal(existsSync(join(directory, "php.invoked")), false);
        assert.equal(existsSync(join(directory, "bun.invoked")), false);
    } finally {
        rmSync(directory, { recursive: true, force: true });
    }
});

for (const [failed, sibling] of [["php", "bun"], ["bun", "php"]]) {
    test(`development launcher propagates ${failed} failure and stops ${sibling}`, () => {
        const directory = mkdtempSync(join(tmpdir(), "dlf-launcher-"));
        try {
            const double = (command) => `#!/usr/bin/env bash
printf '%s' "$$" > "$FIXTURE/${command}.pid"
printf '%s' "$INERTIA_SSR_URL" > "$FIXTURE/${command}.ssr"
if [ "${command}" = "${failed}" ]; then
    while [ ! -f "$FIXTURE/${sibling}.pid" ]; do sleep 0.05; done
    exit 37
fi
trap 'printf stopped > "$FIXTURE/${command}.stopped"; exit 0' TERM
while true; do sleep 0.05; done
`;
            const result = runLauncher(
                directory,
                { APP_PORT: "12345", VITE_PORT: "12346" },
                { php: double("php"), bun: double("bun") },
            );

            assert.equal(result.error, undefined);
            assert.equal(result.status, 37, result.stderr);
            assert.equal(readFileSync(join(directory, `${sibling}.stopped`), "utf8"), "stopped");
            assert.equal(readFileSync(join(directory, "bun.ssr"), "utf8"), "http://127.0.0.1:12347");
            for (const command of [failed, sibling]) {
                const pid = Number(readFileSync(join(directory, `${command}.pid`), "utf8"));
                assert.throws(() => process.kill(pid, 0), { code: "ESRCH" });
            }
        } finally {
            rmSync(directory, { recursive: true, force: true });
        }
    });
}
