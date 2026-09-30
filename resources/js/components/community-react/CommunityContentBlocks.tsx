import { CommunityImage } from "./CommunityImage";

function embedVideoUrl(value: string): string | null {
    try {
        const url = new URL(value);

        if (url.hostname === "youtu.be") {
            return `https://www.youtube-nocookie.com/embed/${url.pathname.slice(1)}`;
        }

        if (url.hostname.includes("youtube.com")) {
            const id = url.searchParams.get("v") ?? url.pathname.split("/").filter(Boolean).at(-1);

            return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
        }

        if (url.hostname === "vimeo.com" || url.hostname.endsWith(".vimeo.com")) {
            const id = url.pathname.split("/").filter(Boolean).at(-1);

            return id ? `https://player.vimeo.com/video/${id}` : null;
        }
    } catch {
        return null;
    }

    return null;
}

export function EmbedConsent() {
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

    const separator = embedded.includes("?") ? "&" : "?";
    const source = embedded.includes("youtube")
        ? `${embedded}${separator}rel=0&modestbranding=1`
        : `${embedded}${separator}byline=0&color=ff2d20&title=0&transparent=1`;

    return (
        <div className="dlf-consent-embed w-full aspect-video mt-half mb-half">
            <iframe
                className="h-full w-full"
                title="Externe video"
                referrerPolicy="strict-origin-when-cross-origin"
                data-consent-src={source}
                allow="autoplay; fullscreen; picture-in-picture; clipboard-write"
                allowFullScreen
                hidden
            />
            <EmbedConsent />
        </div>
    );
}

export function CommunityContentBlocks({
    blocks,
}: {
    blocks: readonly App.Data.Community.ContentBlockData[];
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
                    <CommunityImage
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

        if (block.type === "red_note" && block.html) {
            return (
                <aside
                    className="red-note set"
                    data-cms-html
                    dangerouslySetInnerHTML={{ __html: block.html }}
                    key={key}
                />
            );
        }

        return null;
    });
}
