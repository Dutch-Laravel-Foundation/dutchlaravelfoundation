const rasterImagePattern = /\.(?:avif|bmp|gif|jpe?g|png|webp)$/i;
const mediaDimensionsPattern = /(?:^|[#&])media-(\d+)x(\d+)(?:$|[&#])/i;

const enhancedAttributes = [
    "data-media-cached",
    "data-media-state",
    "data-progressive-media",
    "decoding",
    "height",
    "loading",
    "width",
] as const;

type EnhancedAttribute = (typeof enhancedAttributes)[number];
type AttributeSnapshot = Map<EnhancedAttribute, string | null>;

function isRasterImage(image: HTMLImageElement) {
    const source = image.currentSrc || image.getAttribute("src");

    if (!source) {
        return false;
    }

    try {
        return rasterImagePattern.test(new URL(source, window.location.href).pathname);
    } catch {
        return false;
    }
}

function snapshotAttributes(image: HTMLImageElement): AttributeSnapshot {
    return new Map(
        enhancedAttributes.map((attribute) => [attribute, image.getAttribute(attribute)]),
    );
}

function restoreAttributes(image: HTMLImageElement, attributes: AttributeSnapshot) {
    attributes.forEach((value, attribute) => {
        if (value === null) {
            image.removeAttribute(attribute);
        } else {
            image.setAttribute(attribute, value);
        }
    });
}

function hintedDimensions(image: HTMLImageElement) {
    const match = image.getAttribute("src")?.match(mediaDimensionsPattern);

    if (!match) {
        return;
    }

    return { height: Number(match[2]), width: Number(match[1]) };
}

function applyIntrinsicDimensions(image: HTMLImageElement) {
    if (
        image.width > 0 &&
        image.height > 0 &&
        image.hasAttribute("width") &&
        image.hasAttribute("height")
    ) {
        return;
    }

    const hinted = hintedDimensions(image);
    const width = hinted?.width || image.naturalWidth;
    const height = hinted?.height || image.naturalHeight;

    if (!image.hasAttribute("width") && width > 0) {
        image.width = width;
    }

    if (!image.hasAttribute("height") && height > 0) {
        image.height = height;
    }
}

function markCached(image: HTMLImageElement) {
    try {
        const entries = performance.getEntriesByName(image.currentSrc);
        const entry = entries.at(-1);
        const source = new URL(image.currentSrc, window.location.href);

        if (
            entry instanceof PerformanceResourceTiming &&
            source.origin === window.location.origin &&
            entry.transferSize === 0 &&
            entry.decodedBodySize > 0
        ) {
            image.dataset.mediaCached = "";
        }
    } catch {
        // Resource timing is an optional enhancement.
    }
}

function enhanceImage(image: HTMLImageElement) {
    const attributes = snapshotAttributes(image);
    let cancelled = false;
    let wrapper: HTMLSpanElement | undefined;

    if (!image.closest("[data-progressive-media-frame]")) {
        wrapper = document.createElement("span");
        wrapper.className = "dlf-inline-progressive-media";
        wrapper.dataset.progressiveMediaFrame = "";
        image.before(wrapper);
        wrapper.append(image);
    }

    image.dataset.progressiveMedia = "";
    image.dataset.mediaState = "loading";
    if (!image.hasAttribute("loading")) {
        image.loading = "lazy";
    }
    if (!image.hasAttribute("decoding")) {
        image.decoding = "async";
    }
    applyIntrinsicDimensions(image);

    const reveal = async () => {
        if (image.naturalWidth <= 0) {
            if (!cancelled) {
                image.dataset.mediaState = "failed";
            }

            return;
        }

        applyIntrinsicDimensions(image);
        markCached(image);

        try {
            await image.decode();
        } catch {
            // Loaded pixels remain usable if decode() rejects.
        }

        if (!cancelled) {
            image.dataset.mediaState = "loaded";
        }
    };
    const handleLoad = () => void reveal();
    const handleError = () => {
        if (!cancelled) {
            image.dataset.mediaState = "failed";
        }
    };

    if (image.complete) {
        void reveal();
    } else {
        image.addEventListener("load", handleLoad, { once: true });
        image.addEventListener("error", handleError, { once: true });
    }

    return () => {
        cancelled = true;
        image.removeEventListener("load", handleLoad);
        image.removeEventListener("error", handleError);
        restoreAttributes(image, attributes);

        if (wrapper?.isConnected) {
            wrapper.replaceWith(image);
        }
    };
}

export function enhanceInlineProgressiveMedia(root: HTMLElement) {
    const enhancements = new Map<HTMLImageElement, () => void>();
    const enhancePendingImages = () => {
        [...root.querySelectorAll<HTMLImageElement>("img")]
            .filter(
                (image) =>
                    isRasterImage(image) &&
                    !image.hasAttribute("data-progressive-media") &&
                    !enhancements.has(image),
            )
            .forEach((image) => enhancements.set(image, enhanceImage(image)));
    };
    const observer = new MutationObserver(() => {
        enhancements.forEach((restore, image) => {
            if (!root.contains(image)) {
                restore();
                enhancements.delete(image);
            }
        });
        enhancePendingImages();
    });

    enhancePendingImages();
    observer.observe(root, { childList: true, subtree: true });

    return () => {
        observer.disconnect();
        [...enhancements.values()].reverse().forEach((restore) => restore());
    };
}
