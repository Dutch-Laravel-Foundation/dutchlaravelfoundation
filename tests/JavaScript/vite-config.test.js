import assert from "node:assert/strict";
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
