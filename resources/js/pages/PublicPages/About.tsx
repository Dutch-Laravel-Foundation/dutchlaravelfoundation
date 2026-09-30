import { ContentBlocks, ProgressiveImage, PublicPageFrame } from "@/components/public-pages-react";
import { SmartLink } from "@/components/ui/SmartLink";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };

const work = [
    [
        "/assets/img/proven-secure.svg",
        "Laravel promoten",
        "We laten bedrijven, overheden en onderwijsinstellingen zien waarom Laravel een volwassen en betrouwbare keuze is voor maatwerksoftware.",
    ],
    [
        "/assets/img/boosting-knowledge.svg",
        "Kennis delen",
        "Via events, werkgroepen en artikelen brengen we Laravel-developers samen en houden we de kennis in Nederland op peil.",
    ],
    [
        "/assets/img/assuring-quality.svg",
        "Kwaliteit herkenbaar maken",
        "Ons keurmerk helpt opdrachtgevers professionele Laravel-specialisten te herkennen wanneer zij een webapplicatie laten ontwikkelen.",
    ],
    [
        "/assets/img/future-proof.svg",
        "Leden verbinden",
        "We bouwen aan een netwerk waarin bureaus, zzp’ers en ontwikkelteams ervaringen uitwisselen en elkaar verder helpen.",
    ],
] as const;
const events = [
    [
        "/assets/uploads/events/zandvoort-strand.jpg",
        "LaraFest",
        "LaraFest & LarAwards",
        "Het jaarlijkse community-event",
        "Een dag vol talks, de uitreiking van de LarAwards en volop ruimte om bij te praten met de hele Nederlandse Laravel-community.",
        1596,
        900,
    ],
    [
        "/assets/uploads/events/hackathon.jpeg",
        "Laravel hackathon",
        "Hackathon",
        "Samen bouwen in één dag",
        "Teams werken één dag samen aan nieuwe ideeën, van AI-experimenten tot opensourcebijdragen.",
        5616,
        3744,
    ],
    [
        "/assets/uploads/events/cxo-diner.jpg",
        "CxO-diner",
        "CxO-diner",
        "Doorpraten op managementniveau",
        "Het management van onze leden schuift aan voor gesprekken over de toekomst van maatwerksoftware en de rol van Laravel.",
        1500,
        1524,
    ],
    [
        "/assets/uploads/events/laravel-meetup.jpeg",
        "Bezoekers tijdens een Laravel meetup",
        "Meetups",
        "Samen kennis delen",
        "Technische talks, praktijkverhalen en ontmoetingen bij Laravel-bedrijven door het hele land.",
        3000,
        2000,
    ],
] as const;
const benefits = [
    [
        "/assets/img/boosting-knowledge.svg",
        "Kennisuitwisseling",
        "Voor developers en managers tijdens leuke events.",
    ],
    [
        "/assets/img/assuring-quality.svg",
        "Keurmerk voor je organisatie",
        "Laat opdrachtgevers en sollicitanten zien dat je bij de Laravel community hoort.",
    ],
    [
        "/assets/img/proven-secure.svg",
        "Promotie van het framework",
        "Onder potentiële opdrachtgevers, overheid en onderwijs.",
    ],
    [
        "/assets/img/available-developers.svg",
        "Leads & projecten",
        "Word gematcht met opdrachtgevers via Match je project.",
    ],
    [
        "/assets/img/development-speed.svg",
        "Kortingen",
        "Op deelname aan Laracon en het Laravel Certified Program.",
    ],
    [
        "/assets/img/enterprise-ready.svg",
        "Werkgroepen",
        "Over best practices, onderwijs, diversiteit en Laravel binnen de overheid.",
    ],
    [
        "/assets/img/future-proof.svg",
        "Hosting Hotline",
        "Kosteloze hosting-hulp van onze managed hosting expert.",
    ],
    [
        "/assets/img/proven-secure.svg",
        "Partnertarief Shock Media",
        "Scherp partnertarief en welkomskorting op managed hosting.",
    ],
] as const;
const milestones = [
    [
        "2019",
        "Eerste Laravel Hackathon",
        "De eerste Laravel Hackathon vormt het lanceringsevenement van de Dutch Laravel Foundation.",
    ],
    [
        "2020",
        "Online meetups en eerste showcase",
        "De DLF organiseert diverse online meetups en publiceert de eerste showcase op de website.",
    ],
    [
        "2021",
        "Online meetups",
        "De DLF blijft de community samenbrengen met diverse online meetups.",
    ],
    [
        "2022",
        "Eerste LaraFest, LarAward en CxO-diner",
        "De eerste edities van LaraFest, de LarAward en het CxO-diner vinden plaats.",
    ],
    [
        "2023",
        "Reguliere events en Larabelles",
        "Naast de reguliere evenementen start de DLF een samenwerking met Larabelles.",
    ],
    [
        "2024",
        "Nieuwe partnerships",
        "De stichting gaat verschillende nieuwe partnerships aan, onder andere met JetBrains en Laravel Certified.",
    ],
    [
        "2025",
        "Samenwerking met Shock Media",
        "De DLF start een samenwerking met Shock Media en brengt een bezoek aan Laravel Live Denmark.",
    ],
] as const;

export default function About({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <div className="dlf-community-page dlf-about-page" data-dlf-footer-cta-stage>
                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-labelledby="about-heading"
                >
                    <div className="dlf-community-head">
                        <span className="dlf-community-kicker">{page.title}</span>
                        <h1 id="about-heading" className="dlf-community-title">
                            De kennis- en brancheorganisatie voor Laravel in Nederland
                        </h1>
                        <p className="dlf-community-intro">
                            Met goedkeuring van Laravel-bedenker Taylor Otwell namen zeven bedrijven
                            het initiatief om het gebruik van Laravel in Nederland verder te
                            professionaliseren. In juni 2019 richtten zij de Dutch Laravel
                            Foundation op.
                        </p>
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-community-section--grid dlf-about-work dlf-divider-section"
                    aria-labelledby="about-work-heading"
                >
                    <div className="dlf-about-red-head">
                        <h2 id="about-work-heading">Wat we doen</h2>
                    </div>
                    <div className="dlf-community-grid-2 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        {work.map(([icon, title, copy]) => (
                            <article className="dlf-community-cell" key={title}>
                                <span className="dlf-community-icon">
                                    <img src={icon} width="44" height="44" alt="" loading="lazy" />
                                </span>
                                <h3 className="dlf-community-feature-title">{title}</h3>
                                <p className="dlf-community-feature-copy">{copy}</p>
                            </article>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-community-grid-2 dlf-about-story dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed"
                    aria-labelledby="about-story-heading"
                >
                    <div className="dlf-about-story-copy">
                        <span className="dlf-community-kicker">Ons verhaal</span>
                        <h2
                            id="about-story-heading"
                            className="dlf-community-heading"
                            style={{ color: "#ff2d20" }}
                        >
                            Ontstaan vanuit de community, voor de community
                        </h2>
                        <p className="dlf-community-copy">
                            De stichting heeft twee doelen. Opdrachtgevers die een van onze leden
                            inschakelen, mogen erop vertrouwen dat zij met professionele
                            Laravel-developers werken.
                        </p>
                        <p className="dlf-community-copy">
                            Daarnaast stimuleren we kennisuitwisseling tussen onze leden. Zo helpen
                            we de kennis en kwaliteit van Laravel in Nederland naar een hoger
                            niveau.
                        </p>
                    </div>
                    <figure className="dlf-community-photo" data-progressive-media-frame>
                        <ProgressiveImage
                            src="/assets/uploads/assets/AboutUs_b@2x.jpg"
                            alt="Leden van de Dutch Laravel Foundation tijdens een bijeenkomst"
                            width="1340"
                            height="830"
                            loading="lazy"
                            decoding="async"
                        />
                    </figure>
                </section>
                <section
                    className="dlf-community-red-band dlf-divider-section dlf-divider-theme-inverse"
                    aria-label="Dutch Laravel Foundation in cijfers"
                >
                    <div className="dlf-community-red-inner dlf-about-stats dlf-divider-grid dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        {[
                            [`${page.support.memberCount}+`, "aangesloten leden"],
                            ["4", "events per jaar"],
                            ["100+", "bezoekers bij LaraFest"],
                            ["2019", "actief sinds"],
                        ].map(([value, label]) => (
                            <div className="dlf-about-stat" key={label}>
                                <strong>{value}</strong>
                                <span>{label}</span>
                            </div>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-community-section--grid dlf-divider-section"
                    aria-labelledby="about-events-heading"
                >
                    <div className="dlf-community-head dlf-community-head--divided">
                        <span className="dlf-community-kicker">Events</span>
                        <h2 id="about-events-heading" className="dlf-community-heading">
                            Wat we organiseren
                        </h2>
                        <p className="dlf-community-intro">
                            Ieder jaar organiseren we vier vaste evenementen voor onze leden:
                            LaraFest, de Laravel Hackathon, het CxO-diner en meetups.
                        </p>
                    </div>
                    <div className="dlf-community-grid-2 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        {events.map(([src, alt, kicker, title, copy, width, height]) => (
                            <article className="dlf-community-cell dlf-about-event" key={title}>
                                <figure
                                    className="dlf-about-event__image"
                                    data-progressive-media-frame
                                >
                                    <ProgressiveImage
                                        src={src}
                                        alt={alt}
                                        width={width}
                                        height={height}
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </figure>
                                <div>
                                    <span
                                        className="dlf-community-kicker"
                                        style={{ color: "#ff2d20" }}
                                    >
                                        {kicker}
                                    </span>
                                    <h3>{title}</h3>
                                    <p className="dlf-community-feature-copy">{copy}</p>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-community-section--grid dlf-about-benefits dlf-divider-section"
                    aria-labelledby="about-benefits-heading"
                >
                    <div className="dlf-community-head">
                        <span className="dlf-community-kicker">Lid worden</span>
                        <h2 id="about-benefits-heading" className="dlf-community-heading">
                            Waarom lid worden?
                        </h2>
                        <p className="dlf-community-intro">
                            Zowel bureaus, zzp’ers als ontwikkelteams die met Laravel werken kunnen
                            lid worden. Naast toegang tot een groot netwerk van Laravel-specialisten
                            krijg je exclusief toegang tot events, een keurmerk voor je organisatie
                            en toegang tot leads van potentiële opdrachtgevers.
                        </p>
                        <SmartLink className="dlf-text-link" href="/lid-worden">
                            Bekijk het lidmaatschap <span aria-hidden="true">→</span>
                        </SmartLink>
                    </div>
                    <div
                        className="dlf-lid-benefits dlf-lid-benefits--overview dlf-about-benefits-overview dlf-divider-region dlf-divider-grid dlf-divider-grid--column-rules-only dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1"
                        aria-label="Voordelen voor leden"
                    >
                        {benefits.map(([icon, title, copy]) => (
                            <article className="dlf-lid-benefit" key={title}>
                                <span className="dlf-community-icon">
                                    <img src={icon} width="44" height="44" alt="" loading="lazy" />
                                </span>
                                <strong className="dlf-lid-benefit__title">{title}</strong>
                                <span className="dlf-lid-benefit__copy">{copy}</span>
                            </article>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-community-section--grid dlf-about-board dlf-divider-section"
                    aria-labelledby="about-board-heading"
                >
                    <div className="dlf-community-head dlf-community-head--divided">
                        <span className="dlf-community-kicker">Het bestuur</span>
                        <h2 id="about-board-heading" className="dlf-community-heading">
                            De mensen achter de stichting
                        </h2>
                        <p className="dlf-community-intro">
                            Het bestuur bestaat uit vrijwilligers uit de aangesloten bureaus, die
                            zich naast hun werk inzetten voor de community.
                        </p>
                    </div>
                    <div className="dlf-community-grid-4 dlf-about-board-grid dlf-fill-grid dlf-divider-region dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-3 dlf-divider-grid--mobile-2">
                        {page.support.board.map((member) => (
                            <article
                                className="dlf-community-cell dlf-about-board-card"
                                key={member.id}
                            >
                                <figure
                                    className="dlf-about-board-card__photo"
                                    data-progressive-media-frame
                                >
                                    {member.photo ? (
                                        <ProgressiveImage
                                            src={member.photo.url ?? undefined}
                                            alt={member.name}
                                            style={
                                                member.photo.focusCss
                                                    ? { objectPosition: member.photo.focusCss }
                                                    : undefined
                                            }
                                            width={member.photo.width ?? undefined}
                                            height={member.photo.height ?? undefined}
                                            loading="lazy"
                                            decoding="async"
                                        />
                                    ) : null}
                                </figure>
                                <div className="dlf-about-board-card__copy">
                                    <span className="dlf-about-board-card__name">
                                        {member.name}
                                    </span>
                                    <span className="dlf-about-board-card__function">
                                        {member.function}
                                    </span>
                                </div>
                            </article>
                        ))}
                        <span className="dlf-divider-grid__filler" aria-hidden="true" />
                    </div>
                </section>
                <section
                    className="dlf-community-section dlf-about-milestones dlf-divider-section"
                    aria-labelledby="about-milestones-heading"
                >
                    <div className="dlf-community-head dlf-community-head--compact">
                        <span className="dlf-community-kicker">Mijlpalen</span>
                        <h2 id="about-milestones-heading" className="dlf-community-heading">
                            Belangrijke momenten
                        </h2>
                    </div>
                    <div className="dlf-about-timeline dlf-divider-region dlf-divider-list">
                        {milestones.map(([year, title, copy]) => (
                            <div className="dlf-about-timeline__row" key={year}>
                                <span className="dlf-about-timeline__year">{year}</span>
                                <div>
                                    <h3>{title}</h3>
                                    <p>{copy}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-about-partners dlf-divider-section dlf-divider-section--composite-tail"
                    aria-labelledby="about-partners-heading"
                >
                    <div className="dlf-community-head dlf-community-head--divided">
                        <span className="dlf-community-kicker">Founding partners</span>
                        <h2 id="about-partners-heading" className="dlf-community-heading">
                            De bedrijven van het eerste uur
                        </h2>
                        <p className="dlf-community-intro">
                            De volgende bedrijven hebben in 2019 gezamenlijk gewerkt aan de
                            oprichting van de Dutch Laravel Foundation.
                        </p>
                    </div>
                    <div className="dlf-members-grid dlf-divider-region dlf-divider-grid dlf-divider-tail-segment dlf-divider-grid--desktop-4 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-2">
                        {page.support.foundingPartners.map((partner) => (
                            <SmartLink
                                href={partner.url ?? `/leden/${partner.slug}`}
                                className="dlf-member-card"
                                key={partner.id}
                            >
                                <span className="dlf-member-card__logo">
                                    {partner.logo ? (
                                        <img
                                            src={partner.logo.url ?? undefined}
                                            alt={partner.name}
                                            loading="lazy"
                                        />
                                    ) : null}
                                </span>
                                <span className="dlf-member-card__body">
                                    <span className="dlf-member-card__name">{partner.name}</span>
                                    <span className="dlf-member-card__location">
                                        <span>
                                            {partner.city}
                                            {partner.city && partner.province ? ", " : ""}
                                        </span>
                                        <span className="dlf-member-card__province">
                                            {partner.province}
                                        </span>
                                    </span>
                                </span>
                            </SmartLink>
                        ))}
                    </div>
                </section>
            </div>
            <div className="dlf-rails">
                <ContentBlocks
                    blocks={page.content.filter((block) => block.type.startsWith("dlf_"))}
                    railLevel
                />
            </div>
        </PublicPageFrame>
    );
}
