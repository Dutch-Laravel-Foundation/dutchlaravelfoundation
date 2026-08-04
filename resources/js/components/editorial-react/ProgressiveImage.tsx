import { type ImgHTMLAttributes, type SyntheticEvent, useEffect, useRef, useState } from "react";

type MediaState = "failed" | "loaded" | "loading";

const decodedImages = new Set<string>();
const decodingImages = new Map<string, Promise<void>>();

function decodeImage(image: HTMLImageElement, source: string) {
    const decode = image.decode?.();

    return Promise.resolve(decode)
        .catch(() => undefined)
        .then(() => {
            decodedImages.add(source);
        });
}

export function preloadProgressiveImage(source: string): Promise<void> {
    if (decodedImages.has(source)) {
        return Promise.resolve();
    }

    const pendingImage = decodingImages.get(source);

    if (pendingImage) {
        return pendingImage;
    }

    const preload = new Promise<void>((resolve) => {
        const image = new Image();

        image.onload = () => {
            void decodeImage(image, source).then(resolve);
        };
        image.onerror = () => resolve();
        image.src = source;
    }).finally(() => {
        decodingImages.delete(source);
    });

    decodingImages.set(source, preload);

    return preload;
}

export function ProgressiveImage({
    onError,
    onLoad,
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    const imageRef = useRef<HTMLImageElement>(null);
    const source = typeof props.src === "string" ? props.src : null;
    const initiallyCached = source !== null && decodedImages.has(source);
    const [mediaState, setMediaState] = useState<MediaState>(
        initiallyCached ? "loaded" : "loading",
    );
    const [cached, setCached] = useState(initiallyCached);

    useEffect(() => {
        const image = imageRef.current;

        if (!image || !source) {
            return;
        }

        if (decodedImages.has(source)) {
            setCached(true);
            setMediaState("loaded");

            return;
        }

        setCached(false);
        setMediaState("loading");

        if (image.complete) {
            if (image.naturalWidth === 0) {
                setMediaState("failed");

                return;
            }

            void decodeImage(image, source).then(() => {
                setCached(true);
                setMediaState("loaded");
            });
        }
    }, [source]);

    const handleLoad = (event: SyntheticEvent<HTMLImageElement>) => {
        onLoad?.(event);

        if (!source) {
            setMediaState("loaded");

            return;
        }

        void decodeImage(event.currentTarget, source).then(() => setMediaState("loaded"));
    };
    const handleError = (event: SyntheticEvent<HTMLImageElement>) => {
        setMediaState("failed");
        onError?.(event);
    };

    return (
        <img
            ref={imageRef}
            {...props}
            data-progressive-media
            data-media-cached={cached ? "true" : undefined}
            data-media-state={mediaState}
            onError={handleError}
            onLoad={handleLoad}
        />
    );
}
