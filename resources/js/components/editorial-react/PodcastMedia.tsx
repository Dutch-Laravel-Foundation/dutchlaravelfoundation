import { embedVideoUrl } from "./format";

export function PodcastMedia({ title, videoUrl }: { title: string; videoUrl: string }) {
    const embedded = embedVideoUrl(videoUrl);

    return (
        <figure className="dlf-consent-embed editorial-article__figure editorial-podcast__media">
            {embedded ? (
                <>
                    <iframe
                        data-consent-src={embedded}
                        title={title}
                        referrerPolicy="strict-origin-when-cross-origin"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write"
                        allowFullScreen
                        hidden
                    />
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
                </>
            ) : (
                <video src={videoUrl} controls preload="metadata" aria-label={title} />
            )}
        </figure>
    );
}
