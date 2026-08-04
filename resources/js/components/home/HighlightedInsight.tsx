import { SmartLink } from "@/components/ui/SmartLink";

import { contentUrl, plainText } from "./content";
import { ProgressiveImage } from "./ProgressiveImage";

export function HighlightedInsight({ insight }: { insight: App.Data.Home.ContentCardData }) {
    const url = contentUrl(insight, "nieuws");

    return (
        <section
            className="dlf-home-highlight dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed"
            aria-labelledby="highlight-heading"
        >
            <article>
                <p className="dlf-kicker">Mis het niet</p>
                <h2 id="highlight-heading">
                    <SmartLink href={url}>{insight.title}</SmartLink>
                </h2>
                <div className="dlf-home-excerpt">{plainText(insight.introduction)}</div>
                <SmartLink className="dlf-text-link" href={url}>
                    Lees meer <span aria-hidden="true">→</span>
                </SmartLink>
            </article>
            <figure data-progressive-media-frame>
                {insight.featuredImage ? (
                    <ProgressiveImage
                        src={insight.featuredImage.url}
                        sizes="(min-width: 1280px) 640px, (min-width: 1024px) 50vw, 100vw"
                        alt={insight.featuredImage.alt ?? insight.title}
                        style={{ objectPosition: insight.featuredImage.focusCss ?? undefined }}
                        width="1400"
                        height="900"
                        loading="lazy"
                        decoding="async"
                    />
                ) : (
                    <ProgressiveImage
                        src="/assets/redesign/photos/larafest-groepsfoto.jpeg"
                        alt=""
                        width="3632"
                        height="2406"
                        loading="lazy"
                        decoding="async"
                    />
                )}
            </figure>
        </section>
    );
}
