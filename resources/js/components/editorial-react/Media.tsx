import { ProgressiveImage } from "./ProgressiveImage";

const placeholder = (
    <span className="editorial-media-placeholder" aria-hidden="true">
        <img src="/assets/uploads/globals/LaravelBrandMark.svg" width="135" height="140" alt="" />
    </span>
);

type EditorialImageProps = {
    asset: App.Data.Editorial.AssetData | null;
    className?: string;
    eager?: boolean;
    title: string;
};

export function EditorialImage({ asset, className, eager = false, title }: EditorialImageProps) {
    const source = asset?.url ?? asset?.permalink;

    if (!asset || !source) {
        return placeholder;
    }

    return (
        <ProgressiveImage
            className={className}
            src={source}
            alt={asset.alt ?? title}
            width={asset.width ?? undefined}
            height={asset.height ?? undefined}
            style={{ objectPosition: asset.focusCss ?? undefined }}
            loading={eager ? "eager" : "lazy"}
            fetchPriority={eager ? "high" : undefined}
            decoding="async"
        />
    );
}

export function ExternalImage({
    className,
    eager = false,
    source,
    title,
}: {
    className?: string;
    eager?: boolean;
    source: string;
    title: string;
}) {
    if (!source) {
        return placeholder;
    }

    return (
        <ProgressiveImage
            className={className}
            src={source}
            alt={title}
            width="1280"
            height="720"
            loading={eager ? "eager" : "lazy"}
            fetchPriority={eager ? "high" : undefined}
            decoding="async"
        />
    );
}
