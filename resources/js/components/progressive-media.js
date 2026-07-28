export function waitForImageLoad(image) {
    if (image.complete && image.naturalWidth > 0) {
        return Promise.resolve();
    }

    if (image.complete) {
        return Promise.reject(new Error("Image failed to load"));
    }

    return new Promise((resolve, reject) => {
        image.addEventListener("load", resolve, { once: true });
        image.addEventListener("error", () => reject(new Error("Image failed to load")), {
            once: true,
        });
    });
}

function markCachedImage(image, performanceApi, currentLocation) {
    try {
        const entries = performanceApi?.getEntriesByName(image.currentSrc) ?? [];
        const entry = entries[entries.length - 1];
        const imageUrl = new URL(image.currentSrc, currentLocation?.href);

        if (
            entry &&
            imageUrl.origin === currentLocation?.origin &&
            entry.transferSize === 0 &&
            entry.decodedBodySize > 0
        ) {
            image.dataset.mediaCached = "";
        }
    } catch {
        // Resource timing is an optional enhancement.
    }
}

export async function revealProgressiveImage(
    image,
    performanceApi = globalThis.performance,
    currentLocation = globalThis.location,
) {
    try {
        await waitForImageLoad(image);
    } catch {
        image.dataset.mediaState = "failed";

        return;
    }

    markCachedImage(image, performanceApi, currentLocation);

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
        [...root.querySelectorAll("[data-progressive-media]")].map(revealProgressiveImage),
    );
}
