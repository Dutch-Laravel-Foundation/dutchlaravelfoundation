import { SmartLink } from "@/components/ui/SmartLink";

import { contentUrl, plainText } from "./content";
import { ProgressiveImage } from "./ProgressiveImage";

type CurrentCommunityProps = {
    latestInsight: App.Data.Home.ContentCardData | null;
    latestKnowledge: App.Data.Home.ContentCardData | null;
};

function CardImage({
    asset,
    alt,
    sizes,
    width,
    height,
}: {
    asset: App.Data.Home.AssetData;
    alt: string;
    sizes: string;
    width: number;
    height: number;
}) {
    return (
        <ProgressiveImage
            src={asset.url}
            sizes={sizes}
            alt={asset.alt ?? alt}
            style={{ objectPosition: asset.focusCss ?? undefined }}
            width={width}
            height={height}
            loading="lazy"
            decoding="async"
        />
    );
}

export function CurrentCommunity({ latestInsight, latestKnowledge }: CurrentCommunityProps) {
    const insightUrl = latestInsight ? contentUrl(latestInsight, "nieuws") : null;
    const knowledgeUrl = latestKnowledge ? contentUrl(latestKnowledge, "kennis") : null;

    return (
        <section
            className="dlf-home-current dlf-divider-section dlf-divider-section--yield-to-next"
            aria-labelledby="current-heading"
        >
            <header className="dlf-home-section-head">
                <h2 id="current-heading">Actueel binnen de community</h2>
                <nav aria-label="Actuele publicaties">
                    <SmartLink className="dlf-text-link" href="/nieuws">
                        Al het nieuws <span aria-hidden="true">→</span>
                    </SmartLink>
                    <span aria-hidden="true" />
                    <SmartLink className="dlf-text-link" href="/kennis">
                        Alle kennisartikelen <span aria-hidden="true">→</span>
                    </SmartLink>
                </nav>
            </header>

            <div className="dlf-home-bento">
                {latestInsight && insightUrl ? (
                    <>
                        <SmartLink
                            className="dlf-home-bento__featured-image"
                            href={insightUrl}
                            aria-label={`Lees ${latestInsight.title}`}
                            data-progressive-media-frame
                        >
                            {latestInsight.featuredImage ? (
                                <CardImage
                                    asset={latestInsight.featuredImage}
                                    alt={latestInsight.title}
                                    sizes="(min-width: 1280px) 640px, (min-width: 1024px) 50vw, 100vw"
                                    width={1400}
                                    height={760}
                                />
                            ) : (
                                <ProgressiveImage
                                    src="/assets/redesign/photos/easi-2025.jpg"
                                    alt=""
                                    width="2500"
                                    height="1405"
                                    loading="lazy"
                                    decoding="async"
                                />
                            )}
                        </SmartLink>
                        <SmartLink className="dlf-home-bento__featured-copy" href={insightUrl}>
                            <p className="dlf-kicker">{latestInsight.category ?? "Nieuws"}</p>
                            <h3>{latestInsight.title}</h3>
                            <div className="dlf-home-excerpt">
                                {plainText(latestInsight.introduction, 260)}
                            </div>
                            <span className="dlf-text-link">
                                Lees meer <span aria-hidden="true">→</span>
                            </span>
                        </SmartLink>
                    </>
                ) : null}

                {latestKnowledge && knowledgeUrl ? (
                    <>
                        {latestKnowledge.featuredImage ? (
                            <SmartLink
                                className="dlf-home-bento__knowledge-image"
                                href={knowledgeUrl}
                                aria-label={`Lees ${latestKnowledge.title}`}
                                data-progressive-media-frame
                            >
                                <CardImage
                                    asset={latestKnowledge.featuredImage}
                                    alt={latestKnowledge.title}
                                    sizes="100vw"
                                    width={1000}
                                    height={600}
                                />
                            </SmartLink>
                        ) : null}
                        <article className="dlf-home-bento__knowledge">
                            <p className="dlf-kicker">Kennis</p>
                            <h3>
                                <SmartLink href={knowledgeUrl}>{latestKnowledge.title}</SmartLink>
                            </h3>
                            <div className="dlf-home-excerpt">
                                {plainText(latestKnowledge.introduction, 240)}
                            </div>
                            <SmartLink className="dlf-text-link" href={knowledgeUrl}>
                                Lees artikel <span aria-hidden="true">→</span>
                            </SmartLink>
                        </article>
                    </>
                ) : null}

                <article className="dlf-home-bento__about">
                    <p className="dlf-kicker">Over ons</p>
                    <h3>
                        Een initiatief om het gebruik van Laravel in Nederland verder te
                        professionaliseren.
                    </h3>
                    <p>
                        Met goedkeuring van Taylor Otwell heeft een zevental bedrijven het
                        initiatief genomen; in juni 2019 is de Dutch Laravel Foundation opgericht.
                    </p>
                    <SmartLink className="dlf-text-link" href="/over-ons">
                        Lees meer over ons <span aria-hidden="true">→</span>
                    </SmartLink>
                </article>
            </div>
        </section>
    );
}
