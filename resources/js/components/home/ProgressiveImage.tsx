import { type ImgHTMLAttributes, type SyntheticEvent, useEffect, useRef, useState } from "react";

type MediaState = "failed" | "loaded" | "loading";

type ProgressiveImageProps = ImgHTMLAttributes<HTMLImageElement>;

export function ProgressiveImage({ onError, onLoad, ...props }: ProgressiveImageProps) {
    const imageRef = useRef<HTMLImageElement>(null);
    const [mediaState, setMediaState] = useState<MediaState>("loading");

    useEffect(() => {
        const image = imageRef.current;

        if (!image?.complete) {
            return;
        }

        setMediaState(image.naturalWidth > 0 ? "loaded" : "failed");
    }, [props.src]);

    const handleLoad = (event: SyntheticEvent<HTMLImageElement>) => {
        setMediaState("loaded");
        onLoad?.(event);
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
            data-media-state={mediaState}
            onError={handleError}
            onLoad={handleLoad}
        />
    );
}
