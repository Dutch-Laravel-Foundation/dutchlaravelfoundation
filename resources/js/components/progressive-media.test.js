import { describe, expect, it, mock } from "bun:test";

import {
    initProgressiveMedia,
    revealProgressiveImage,
} from "./progressive-media";

function makeImage({ complete = false, naturalWidth = 0 } = {}) {
    const image = new EventTarget();
    image.complete = complete;
    image.naturalWidth = naturalWidth;
    image.dataset = { mediaState: "loading" };
    image.decode = mock(() => Promise.resolve());

    return image;
}

describe("progressive media", () => {
    it("reveals an already cached image after decoding", async () => {
        const image = makeImage({ complete: true, naturalWidth: 1200 });

        await revealProgressiveImage(image);

        expect(image.decode).toHaveBeenCalledTimes(1);
        expect(image.dataset.mediaState).toBe("loaded");
    });

    it("waits for a pending image before decoding and revealing it", async () => {
        const image = makeImage();
        const reveal = revealProgressiveImage(image);

        image.complete = true;
        image.naturalWidth = 1200;
        image.dispatchEvent(new Event("load"));
        await reveal;

        expect(image.decode).toHaveBeenCalledTimes(1);
        expect(image.dataset.mediaState).toBe("loaded");
    });

    it("keeps the placeholder when loading fails", async () => {
        const image = makeImage();
        const reveal = revealProgressiveImage(image);

        image.dispatchEvent(new Event("error"));
        await reveal;

        expect(image.decode).not.toHaveBeenCalled();
        expect(image.dataset.mediaState).toBe("failed");
    });

    it("reveals a loaded image when decoding fails", async () => {
        const image = makeImage({ complete: true, naturalWidth: 1200 });
        image.decode = mock(() => Promise.reject(new Error("decode failed")));

        await revealProgressiveImage(image);

        expect(image.dataset.mediaState).toBe("loaded");
    });

    it("initializes only explicitly marked images", async () => {
        const image = makeImage({ complete: true, naturalWidth: 1200 });
        const root = {
            querySelectorAll: mock(() => [image]),
        };

        await initProgressiveMedia(root);

        expect(root.querySelectorAll).toHaveBeenCalledWith(
            "[data-progressive-media]",
        );
        expect(image.dataset.mediaState).toBe("loaded");
    });
});
