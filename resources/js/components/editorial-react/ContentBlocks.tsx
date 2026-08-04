import { EditorialImage } from "./Media";
import { embedVideoUrl } from "./format";

function EmbedConsent() {
    return (
        <div className="dlf-consent-embed__placeholder" data-consent-placeholder>
            <span className="dlf-consent-embed__eyebrow">Externe video</span>
            <p>Geef toestemming voor externe media om deze video te bekijken.</p>
            <button
                type="button"
                data-tracking-consent-settings
                data-tracking-consent-embed-settings
                hidden
            >
                Cookievoorkeuren aanpassen
            </button>
        </div>
    );
}

function VideoBlock({ value }: { value: string }) {
    const embedded = embedVideoUrl(value);

    if (!embedded) {
        return <video className="w-full aspect-video mt-half mb-half" src={value} controls />;
    }

    return (
        <div className="dlf-consent-embed w-full aspect-video mt-half mb-half">
            <iframe
                className="h-full w-full"
                title="Externe video"
                referrerPolicy="strict-origin-when-cross-origin"
                data-consent-src={embedded}
                allow="autoplay; fullscreen; picture-in-picture; clipboard-write"
                allowFullScreen
                hidden
            />
            <EmbedConsent />
        </div>
    );
}

export function ContentBlocks({
    blocks,
}: {
    blocks: readonly App.Data.Editorial.ContentBlockData[];
}) {
    return blocks.map((block, index) => {
        const key = block.id ?? `${block.kind}-${index}`;

        if (block.type === "text" && block.html) {
            return (
                <div
                    className="set"
                    data-cms-html
                    dangerouslySetInnerHTML={{ __html: block.html }}
                    key={key}
                />
            );
        }

        if (block.type === "image" && block.asset) {
            return (
                <figure className="mb-10" data-progressive-media-frame key={key}>
                    <EditorialImage
                        asset={block.asset}
                        className="object-cover w-full"
                        title={block.asset.alt ?? ""}
                    />
                </figure>
            );
        }

        if (block.type === "spacer") {
            return <div className="set mb-half" key={key} />;
        }

        if (block.type === "line") {
            return (
                <div className="h-10 border-b border-tertiary-dark opacity-20 mb-half" key={key} />
            );
        }

        if (block.type === "video" && block.value) {
            return <VideoBlock value={block.value} key={key} />;
        }

        return null;
    });
}
