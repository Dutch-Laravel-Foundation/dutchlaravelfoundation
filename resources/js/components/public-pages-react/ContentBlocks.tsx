import { Button } from "@base-ui/react/button";
import { Fragment } from "react";

import { SmartLink } from "@/components/ui/SmartLink";

import { ProgressiveImage } from "./ProgressiveImage";

type Block = App.Data.PublicPages.ContentBlockData;
type Asset = App.Data.PublicPages.AssetData;

function Html({ className, html }: { className?: string; html?: string | null }) {
    return html ? (
        <div className={className} data-cms-html dangerouslySetInnerHTML={{ __html: html }} />
    ) : null;
}

function AssetImage({
    asset,
    alt,
    progressive = false,
}: {
    asset: Asset;
    alt?: string | null;
    progressive?: boolean;
}) {
    const Image = progressive ? ProgressiveImage : "img";

    return (
        <Image
            src={asset.url ?? asset.permalink ?? undefined}
            alt={asset.alt ?? alt ?? ""}
            width={asset.width ?? undefined}
            height={asset.height ?? undefined}
            loading="lazy"
            decoding="async"
            style={asset.focusCss ? { objectPosition: asset.focusCss } : undefined}
        />
    );
}

function ActionLink({ action }: { action: App.Data.PublicPages.ActionData }) {
    return (
        <SmartLink className="dlf-text-link" href={action.link.url}>
            {action.label} <span aria-hidden="true">→</span>
        </SmartLink>
    );
}

function DlfBlock({ block }: { block: Block }) {
    if (block.type === "dlf_hero") {
        const Heading = block.headingLevel === "h1" ? "h1" : "h2";
        const position = block.imagePosition ?? "right";

        return (
            <section
                className={`dlf-block dlf-block-hero dlf-block-hero--${position} dlf-divider-section dlf-divider-split${position === "left" ? " dlf-divider-split--desktop-reversed" : ""}`}
            >
                <div className="dlf-block-hero__content">
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <Heading>{block.heading}</Heading> : null}
                    <Html className="dlf-prose" html={block.bodyHtml} />
                    {block.primaryAction || block.secondaryAction ? (
                        <div className="dlf-actions">
                            {block.primaryAction ? (
                                <SmartLink
                                    className="dlf-button dlf-button--primary"
                                    href={block.primaryAction.link.url}
                                >
                                    <span>{block.primaryAction.label}</span>
                                </SmartLink>
                            ) : null}
                            {block.secondaryAction ? (
                                <SmartLink
                                    className="dlf-button dlf-button--outline"
                                    href={block.secondaryAction.link.url}
                                >
                                    <span>{block.secondaryAction.label}</span>
                                </SmartLink>
                            ) : null}
                        </div>
                    ) : null}
                </div>
                {block.asset ? (
                    <figure className="dlf-block-hero__media" data-progressive-media-frame>
                        <AssetImage asset={block.asset} alt={block.heading} progressive />
                    </figure>
                ) : null}
            </section>
        );
    }

    if (block.type === "dlf_media_text") {
        const position = block.imagePosition ?? "right";
        const tone = block.tone ?? "white";
        return (
            <section
                className={`dlf-block dlf-block-split dlf-block-split--${position} dlf-block--${tone} dlf-divider-section dlf-divider-split${position === "left" ? " dlf-divider-split--desktop-reversed" : ""}${tone === "red" || tone === "dark" ? " dlf-divider-theme-inverse" : ""}`}
            >
                <div className="dlf-block-split__content">
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <h2>{block.heading}</h2> : null}
                    <Html className="dlf-prose" html={block.bodyHtml} />
                    {block.primaryAction ? <ActionLink action={block.primaryAction} /> : null}
                </div>
                {block.asset ? (
                    <figure className="dlf-block-split__media" data-progressive-media-frame>
                        <AssetImage asset={block.asset} alt={block.heading} progressive />
                    </figure>
                ) : null}
            </section>
        );
    }

    if (block.type === "dlf_feature_grid") {
        const columns = block.columns ?? "3";
        return (
            <section className="dlf-block dlf-block-features dlf-divider-section">
                <header className="dlf-block-heading">
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <h2>{block.heading}</h2> : null}
                    <Html className="dlf-prose" html={block.introductionHtml} />
                </header>
                <div
                    className={`dlf-feature-grid dlf-feature-grid--${columns} dlf-divider-grid dlf-divider-grid--framed dlf-divider-grid--desktop-${columns} dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1`}
                >
                    {block.features.map((feature) => (
                        <article className="dlf-feature-card" key={feature.id ?? feature.heading}>
                            {feature.icon ? <AssetImage asset={feature.icon} /> : null}
                            <h3>{feature.heading}</h3>
                            <Html className="dlf-prose" html={feature.bodyHtml} />
                            {feature.action ? <ActionLink action={feature.action} /> : null}
                        </article>
                    ))}
                </div>
            </section>
        );
    }

    if (block.type === "dlf_card_grid") {
        return (
            <section className="dlf-block dlf-block-cards dlf-divider-section">
                <header className="dlf-block-heading">
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <h2>{block.heading}</h2> : null}
                    <Html className="dlf-prose" html={block.introductionHtml} />
                </header>
                <div className="dlf-card-grid dlf-divider-grid dlf-divider-grid--framed dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                    {block.cards.map((card) => (
                        <article className="dlf-content-card" key={card.id ?? card.heading}>
                            {card.image ? (
                                <figure
                                    className="dlf-content-card__media"
                                    data-progressive-media-frame
                                >
                                    <AssetImage asset={card.image} alt={card.heading} progressive />
                                </figure>
                            ) : null}
                            <div className="dlf-content-card__body">
                                {card.eyebrow ? <p className="dlf-kicker">{card.eyebrow}</p> : null}
                                <h3>{card.heading}</h3>
                                <Html className="dlf-prose" html={card.bodyHtml} />
                                {card.action ? <ActionLink action={card.action} /> : null}
                            </div>
                        </article>
                    ))}
                </div>
            </section>
        );
    }

    if (block.type === "dlf_stats") {
        return (
            <section className="dlf-block dlf-block-stats dlf-divider-section">
                <header className="dlf-block-heading">
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <h2>{block.heading}</h2> : null}
                    <Html className="dlf-prose" html={block.introductionHtml} />
                </header>
                <div className="dlf-stats-grid dlf-divider-grid dlf-divider-grid--framed dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                    {block.stats.map((stat) => (
                        <article
                            className="dlf-stat"
                            key={stat.id ?? `${stat.value}-${stat.label}`}
                        >
                            <strong>{stat.value}</strong>
                            <h3>{stat.label}</h3>
                            {stat.context ? <p>{stat.context}</p> : null}
                        </article>
                    ))}
                </div>
            </section>
        );
    }

    if (block.type === "dlf_quote") {
        return (
            <figure className={`dlf-block dlf-quote dlf-block--${block.tone ?? "soft"}`}>
                {block.asset ? (
                    <span className="dlf-quote__media" data-progressive-media-frame>
                        <AssetImage asset={block.asset} alt={block.attributionName} progressive />
                    </span>
                ) : null}
                <div>
                    <blockquote>“{block.quote}”</blockquote>
                    {block.attributionName || block.attributionRole ? (
                        <figcaption>
                            {block.attributionName ? (
                                <strong>{block.attributionName}</strong>
                            ) : null}
                            {block.attributionRole ? <span>{block.attributionRole}</span> : null}
                        </figcaption>
                    ) : null}
                </div>
            </figure>
        );
    }

    if (block.type === "dlf_logo_cloud") {
        return (
            <section className="dlf-block dlf-block-logos dlf-divider-section">
                {block.heading ? <h2>{block.heading}</h2> : null}
                <div className="dlf-logo-grid dlf-divider-grid dlf-divider-grid--framed dlf-divider-grid--desktop-5 dlf-divider-grid--tablet-3 dlf-divider-grid--mobile-2">
                    {block.logos.map((logo) => {
                        const image = logo.asset ? (
                            <AssetImage asset={logo.asset} alt={logo.name} />
                        ) : null;
                        return logo.link ? (
                            <SmartLink
                                className="dlf-logo-cell"
                                href={logo.link.url}
                                aria-label={logo.name}
                                key={logo.id ?? logo.name}
                            >
                                {image}
                            </SmartLink>
                        ) : (
                            <div className="dlf-logo-cell" key={logo.id ?? logo.name}>
                                {image}
                            </div>
                        );
                    })}
                </div>
            </section>
        );
    }

    if (block.type === "dlf_pricing") {
        return (
            <section className="dlf-block dlf-block-pricing dlf-divider-section">
                <header className="dlf-block-heading">
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <h2>{block.heading}</h2> : null}
                    <Html className="dlf-prose" html={block.introductionHtml} />
                </header>
                <div className="dlf-pricing-grid dlf-divider-grid dlf-divider-grid--framed dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                    {block.plans.map((plan) => (
                        <article
                            className={`dlf-price-card${plan.featured ? " dlf-price-card--featured" : ""}`}
                            key={plan.id ?? plan.name}
                        >
                            {plan.featured ? <p className="dlf-kicker">Aanbevolen</p> : null}
                            <h3>{plan.name}</h3>
                            {plan.price ? (
                                <p className="dlf-price-card__price">
                                    {plan.price} {plan.suffix ? <small>{plan.suffix}</small> : null}
                                </p>
                            ) : null}
                            <Html className="dlf-prose" html={plan.descriptionHtml} />
                            {plan.features.length ? (
                                <ul>
                                    {plan.features.map((feature) => (
                                        <li key={feature}>{feature}</li>
                                    ))}
                                </ul>
                            ) : null}
                            {plan.action ? (
                                <SmartLink
                                    className="dlf-button dlf-button--primary"
                                    href={plan.action.link.url}
                                >
                                    <span>{plan.action.label}</span>
                                </SmartLink>
                            ) : null}
                        </article>
                    ))}
                </div>
            </section>
        );
    }

    if (block.type === "dlf_cta_panel") {
        const tone = block.tone ?? "red";
        return (
            <section
                className={`dlf-block dlf-cta-panel dlf-block--${tone} dlf-divider-section${tone === "red" || tone === "dark" ? " dlf-divider-theme-inverse" : ""}`}
            >
                <div>
                    {block.eyebrow ? <p className="dlf-kicker">{block.eyebrow}</p> : null}
                    {block.heading ? <h2>{block.heading}</h2> : null}
                    <Html className="dlf-prose" html={block.bodyHtml} />
                </div>
                {block.primaryAction || block.secondaryAction ? (
                    <div className="dlf-actions">
                        {block.primaryAction ? (
                            <SmartLink
                                className="dlf-button dlf-button--light"
                                href={block.primaryAction.link.url}
                            >
                                <span>{block.primaryAction.label}</span>
                            </SmartLink>
                        ) : null}
                        {block.secondaryAction ? (
                            <ActionLink action={block.secondaryAction} />
                        ) : null}
                    </div>
                ) : null}
            </section>
        );
    }

    return null;
}

export function ContentBlocks({
    blocks,
    railLevel = false,
}: {
    blocks: Block[];
    railLevel?: boolean;
}) {
    return blocks.map((block, index) => {
        if (block.type.startsWith("dlf_")) {
            return <DlfBlock block={block} key={block.id ?? `${block.type}-${index}`} />;
        }

        if (railLevel) {
            return null;
        }

        if (block.type === "text")
            return block.html ? (
                <div
                    className="set"
                    data-cms-html
                    dangerouslySetInnerHTML={{ __html: block.html }}
                    key={index}
                />
            ) : null;
        if (block.type === "image" && block.asset)
            return (
                <figure className="mb-10" data-progressive-media-frame key={block.id ?? index}>
                    <AssetImage asset={block.asset} progressive />
                </figure>
            );
        if (block.type === "double_column")
            return (
                <Fragment key={block.id ?? index}>
                    <Html
                        className="w-full px-0 set set-double-column lg:w-1/2 lg:mr-auto"
                        html={block.headingHtml}
                    />
                    <div className="flex flex-col justify-start px-0 set set-double-column lg:flex-row mb-half">
                        <div className="w-full md:w-3/4 lg:w-1/2 lg:pr-10">
                            <ContentBlocks blocks={block.left} />
                        </div>
                        <div className="w-full pt-4 md:w-3/4 lg:w-1/2 lg:pt-0 lg:pl-10">
                            <ContentBlocks blocks={block.right} />
                        </div>
                    </div>
                </Fragment>
            );
        if (block.type === "3_4_column")
            return (
                <Fragment key={block.id ?? index}>
                    <Html
                        className="w-full mr-auto set set-3-4-column sm:px-0 md:w-3/4"
                        html={block.headingHtml}
                    />
                    <div className="w-full set set-3-4-column sm:px-0 md:w-3/4 mb">
                        <ContentBlocks blocks={block.content} />
                    </div>
                </Fragment>
            );
        if (block.type === "meta_block")
            return (
                <div className="set w-full md:w-1/3" key={block.id ?? index}>
                    <span className="meta-dark">{block.title}</span>
                    <p>{block.text}</p>
                </div>
            );
        if (block.type === "red_note")
            return (
                <div
                    className="border border-primary-accent mt-16 red-note bg-white relative z-10"
                    key={block.id ?? index}
                >
                    <Html
                        className="bg-primary-accent p-8 translate-x-2 -translate-y-2 text-white"
                        html={block.bodyHtml}
                    />
                </div>
            );
        if (block.type === "spacer")
            return (
                <div
                    aria-hidden="true"
                    style={{ height: block.value ?? undefined }}
                    key={block.id ?? index}
                />
            );
        if (block.type === "line") return <hr key={block.id ?? index} />;
        if (block.type === "video" && block.value) {
            const embedUrl = block.value.replace("watch?v=", "embed/");

            return (
                <div
                    className="dlf-consent-embed w-full aspect-video mt-half mb-half"
                    key={block.id ?? index}
                >
                    <iframe
                        className="h-full w-full"
                        referrerPolicy="strict-origin-when-cross-origin"
                        data-consent-src={embedUrl}
                        title="Externe video"
                        hidden
                    />
                    <div className="dlf-consent-embed__placeholder" data-consent-placeholder>
                        <span className="dlf-consent-embed__eyebrow">Externe video</span>
                        <p>Geef toestemming voor externe media om deze video te bekijken.</p>
                        <Button
                            type="button"
                            data-tracking-consent-settings
                            data-tracking-consent-embed-settings
                            hidden
                        >
                            Cookievoorkeuren aanpassen
                        </Button>
                    </div>
                </div>
            );
        }
        return null;
    });
}
