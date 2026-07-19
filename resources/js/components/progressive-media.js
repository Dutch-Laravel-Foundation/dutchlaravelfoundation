export function waitForImageLoad(image) {
    if (image.complete && image.naturalWidth > 0) {
        return Promise.resolve();
    }

    if (image.complete) {
        return Promise.reject(new Error("Image failed to load"));
    }

    return new Promise((resolve, reject) => {
        image.addEventListener("load", resolve, { once: true });
        image.addEventListener(
            "error",
            () => reject(new Error("Image failed to load")),
            { once: true },
        );
    });
}

export async function revealProgressiveImage(image) {
    try {
        await waitForImageLoad(image);
    } catch {
        image.dataset.mediaState = "failed";

        return;
    }

    if (typeof image.decode === "function") {
        try {
            await image.decode();
        } catch {
            // A successful load still has usable pixels when decode() rejects.
        }
    }

    image.dataset.mediaState = "loaded";
}

export function initProgressiveMedia(root = document) {
    return Promise.all(
        [...root.querySelectorAll("[data-progressive-media]")].map(
            revealProgressiveImage,
        ),
    );
}
