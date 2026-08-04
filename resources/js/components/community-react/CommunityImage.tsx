import { ProgressiveImage } from "@/components/editorial-react/ProgressiveImage";

type CommunityImageProps = {
    asset: App.Data.Community.AssetData | null;
    className?: string;
    eager?: boolean;
    title: string;
};

export function CommunityImage({ asset, className, eager = false, title }: CommunityImageProps) {
    const source = asset?.url ?? asset?.permalink;

    if (!asset || !source) {
        return (
            <span className="editorial-media-placeholder" aria-hidden="true">
                <img
                    src="/assets/uploads/globals/LaravelBrandMark.svg"
                    width="135"
                    height="140"
                    alt=""
                />
            </span>
        );
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
