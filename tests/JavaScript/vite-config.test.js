import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";

import createViteConfig from "../../vite.config.js";

test("development assets avoid nested runtimes without a public asset proxy", () => {
    const previousAppUrl = process.env.VITE_APP_URL;
    process.env.VITE_APP_URL = "https://dutchlaravelfoundation.test";

    try {
        const config = createViteConfig("development");

        assert.deepEqual(config.server.watch.ignored, ["**/.worktrees/**", "**/vendor/**"]);
        assert.equal(config.server.proxy, undefined);
    } finally {
        if (previousAppUrl === undefined) {
            delete process.env.VITE_APP_URL;
        } else {
            process.env.VITE_APP_URL = previousAppUrl;
        }
    }
});

test("the Inertia SSR renderer only listens on loopback", () => {
    const source = readFileSync(new URL("../../vite.config.ts", import.meta.url), "utf8");

    assert.match(source, /ssr:\s*\{\s*host:\s*["']127\.0\.0\.1["']/);
});
