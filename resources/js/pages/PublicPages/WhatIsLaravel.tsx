import { ContentBlocks, PublicPageFrame } from "@/components/public-pages-react";
import { SmartLink } from "@/components/ui/SmartLink";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };

const benefits = [
    [
        "/assets/img/development-speed.svg",
        "Snelle ontwikkeling",
        "Veelgebruikte functionaliteit zit ingebouwd, waardoor een eerste versie snel live staat.",
    ],
    [
        "/assets/img/proven-secure.svg",
        "Bewezen veilig",
        "Bescherming tegen veelvoorkomende kwetsbaarheden zit standaard in het framework.",
    ],
    [
        "/assets/img/future-proof.svg",
        "Toekomstvast",
        "Voorspelbare releases en duidelijke upgradepaden houden applicaties jarenlang bij de tijd.",
    ],
    [
        "/assets/img/enterprise-ready.svg",
        "Enterprise-ready",
        "Schaalt van MVP tot bedrijfskritisch platform, met tooling voor testen en monitoring.",
    ],
    [
        "/assets/img/boosting-knowledge.svg",
        "Sterke community",
        "Een wereldwijde community deelt kennis via documentatie, packages en conferenties.",
    ],
    [
        "/assets/img/available-developers.svg",
        "Beschikbare developers",
        "In Nederland werken meer dan 5.000 Laravel developers, waardoor je nooit vastzit aan één partij.",
    ],
] as const;

const reading = [
    [
        "Cases",
        "/cases",
        "Bekijk wat Nederlandse organisaties met Laravel bouwen",
        "Ontdek maatwerkapplicaties en digitale platforms die dagelijks met Laravel worden aangedreven.",
        "Bekijk cases",
    ],
    [
        "Kennis",
        "/kennis",
        "Verdiep je in het framework en zijn ecosysteem",
        "Praktische kennis voor beslissers en developers, gedeeld door specialisten uit onze community.",
        "Lees meer",
    ],
    [
        "Aanbestedingen",
        "/aanbestedingen",
        "Laravel opnemen in je aanbesteding of tender",
        "Handvatten voor overheden en organisaties die open source maatwerk uitvragen.",
        "Lees meer",
    ],
] as const;

export default function WhatIsLaravel({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <div className="dlf-community-page dlf-laravel-page" data-dlf-footer-cta-stage>
                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-labelledby="laravel-heading"
                >
                    <div className="dlf-community-head">
                        <span className="dlf-community-kicker">Laravel</span>
                        <h1 id="laravel-heading" className="dlf-community-title">
                            {page.title}
                        </h1>
                        <p className="dlf-community-intro">
                            Laravel is een{" "}
                            <SmartLink
                                href="https://laravel.com"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                open source PHP framework
                            </SmartLink>{" "}
                            voor het bouwen van maatwerk webapplicaties. Denk aan interne tools en
                            platforms met miljoenen gebruikers.
                        </p>
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-laravel-split dlf-divider-section dlf-divider-split"
                    aria-labelledby="laravel-explanation-heading"
                >
                    <div className="dlf-laravel-explanation">
                        <h2
                            id="laravel-explanation-heading"
                            className="dlf-community-heading dlf-community-heading--small"
                        >
                            Het meest gebruikte PHP framework ter wereld
                        </h2>
                        <p className="dlf-community-copy">
                            Laravel bestaat sinds 2011 en biedt kant-en-klare onderdelen voor
                            veelvoorkomende functies, zoals authenticatie, databasekoppelingen,
                            e-mail en wachtrijen. Daardoor kunnen developers sneller bouwen en meer
                            aandacht besteden aan wat een applicatie uniek maakt.
                        </p>
                        <p className="dlf-community-copy">
                            Het framework werkt met duidelijke conventies, waardoor code herkenbaar
                            en goed overdraagbaar blijft. Het volwassen ecosysteem voor testen,
                            hosting, deployment en monitoring groeit mee met een project, van een
                            eerste MVP tot een bedrijfskritisch platform.
                        </p>
                    </div>
                    <div className="dlf-laravel-facts">
                        {[
                            ["2011", "eerste release, gemaakt door Taylor Otwell"],
                            ["#1", "meest gebruikte PHP framework wereldwijd"],
                            [
                                `${page.support.memberCount}+`,
                                "Nederlandse Laravel specialisten aangesloten bij de foundation",
                            ],
                        ].map(([value, text], index) => (
                            <div className="dlf-laravel-fact" key={value}>
                                <div>
                                    <strong>{value}</strong>
                                    <span className="dlf-laravel-fact__copy">{text}</span>
                                </div>
                                <span className="dlf-laravel-fact__index">0{index + 1}</span>
                            </div>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-laravel-benefits dlf-divider-section"
                    aria-labelledby="laravel-benefits-heading"
                >
                    <div className="dlf-community-head dlf-community-head--compact">
                        <h2
                            id="laravel-benefits-heading"
                            className="dlf-community-heading dlf-community-heading--small"
                        >
                            Waarom organisaties voor Laravel kiezen
                        </h2>
                    </div>
                    <div className="dlf-community-grid-3 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        {benefits.map(([icon, title, copy]) => (
                            <article className="dlf-community-cell dlf-laravel-feature" key={title}>
                                <span className="dlf-community-icon">
                                    <img src={icon} width="44" height="44" alt="" loading="lazy" />
                                </span>
                                <h3>{title}</h3>
                                <p className="dlf-community-feature-copy">{copy}</p>
                            </article>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-laravel-reading dlf-divider-section"
                    aria-labelledby="laravel-reading-heading"
                >
                    <div className="dlf-community-head dlf-community-head--compact">
                        <h2
                            id="laravel-reading-heading"
                            className="dlf-community-heading dlf-community-heading--small"
                        >
                            Verder lezen over Laravel
                        </h2>
                    </div>
                    <div className="dlf-community-grid-3 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        {reading.map(([eyebrow, href, title, copy, label]) => (
                            <article className="dlf-community-cell dlf-laravel-link" key={title}>
                                <span className="dlf-community-kicker">{eyebrow}</span>
                                <h3>
                                    <SmartLink href={href}>{title}</SmartLink>
                                </h3>
                                <p>{copy}</p>
                                <SmartLink href={href} className="dlf-community-text-link">
                                    {label} <span aria-hidden="true">→</span>
                                </SmartLink>
                            </article>
                        ))}
                    </div>
                </section>
            </div>
            <div className="dlf-rails">
                <ContentRailBlocks page={page} />
            </div>
        </PublicPageFrame>
    );
}

function ContentRailBlocks({ page }: { page: App.Data.PublicPages.PublicPageData }) {
    const blocks = page.content.filter((block) => block.type.startsWith("dlf_"));
    if (!blocks.length) return null;
    return <ContentBlocks blocks={blocks} railLevel />;
}
