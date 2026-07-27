import { describe, expect, it } from "bun:test";

import { copyCode } from "./code-copy";

describe("copyCode", () => {
    it("copies the complete code block text to the clipboard", async () => {
        const copied = [];
        const code = {
            textContent: "# Laravel Boost skill\n\n## Core Guidance",
        };
        const button = {
            closest: () => ({
                querySelector: () => code,
            }),
        };
        const clipboard = {
            writeText: async (value) => copied.push(value),
        };

        const result = await copyCode(button, clipboard);

        expect(result).toBe(true);
        expect(copied).toEqual(["# Laravel Boost skill\n\n## Core Guidance"]);
    });
});
