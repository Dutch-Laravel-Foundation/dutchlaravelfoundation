import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";

test("development assets avoid nested runtimes without a public asset proxy", () => {
    const source = readFileSync(new URL("../../vite.config.ts", import.meta.url), "utf8");

    assert.match(source, /resolve\(process\.cwd\(\), "\.worktrees\/\*\*"\)/);
    assert.doesNotMatch(source, /"\*\*\/\.worktrees\/\*\*"/);
    assert.doesNotMatch(source, /proxy\s*:/);
});

test("the Inertia SSR renderer only listens on loopback", () => {
    const source = readFileSync(new URL("../../vite.config.ts", import.meta.url), "utf8");

    assert.match(source, /ssr:\s*\{\s*host:\s*["']127\.0\.0\.1["']/);
});

test("Vite has no legacy Statamic or Alpine frontend entrypoint", () => {
    const source = readFileSync(new URL("../../vite.config.ts", import.meta.url), "utf8");
    const packageJson = JSON.parse(
        readFileSync(new URL("../../package.json", import.meta.url), "utf8"),
    );

    assert.doesNotMatch(source, /resources\/js\/statamic\.js/);
    assert.equal(packageJson.dependencies?.["@alpinejs/csp"], undefined);
});
