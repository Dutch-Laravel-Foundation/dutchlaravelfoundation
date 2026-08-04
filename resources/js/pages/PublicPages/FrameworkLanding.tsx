import {
    ContactSection,
    FoundationClose,
    LandingCases,
    PhotoBand,
    PublicPageFrame,
} from "@/components/public-pages-react";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };
const advantages = [
    [
        "Van concept naar MVP in no time in plaats van maanden",
        "Laravel-ontwikkelaars gebruiken bestaande componenten die al compleet doorgetest zijn uit een uniek rijke bibliotheek van duizenden packages. Hierdoor bestaat zo'n 25 tot 75% van je maatwerkapplicatie uit kant-en-klare bouwblokken, zoals:",
        [
            "Inloggen en gebruikersbeheer",
            "Data-encryptie en beveiligingsprotocollen",
            "Koppelingen met andere systemen",
            "Gegevensopslag en rapporten",
            "E-mail berichten en meldingen",
            "Bestandsopslag en documenten",
            "Betalingen en facturatie",
            "Compliance ondersteuning",
            "API architectuur",
        ],
    ],
    [
        "Performance en schaalbaarheid die met je business meegroeien",
        "Niets is frustrerender dan een systeem dat traag wordt naarmate je bedrijf groeit. Laravel maakt het mogelijk dat prestaties stabiel blijven, of je nu 100 of 100.000 gebruikers hebt.",
        [
            "Snelle laadtijden: ook bij meer gebruikers en data",
            "Geen wachttijden: zware processen draaien op de achtergrond",
            "Alles snel vindbaar: slimme data-opslag en caching",
            "Groeit probleemloos mee: schaal van start-up naar enterprise niveau",
        ],
    ],
    [
        "Business continuity gegarandeerd",
        "Het ergste scenario voor elke IT-manager: vastzitten aan één leverancier die je laat vallen, onbetaalbaar wordt of niet meer voldoet. Met Laravel voorkom je vendor lock-in en behoud je altijd de controle over je eigen systeem.",
        [
            "Open source framework: jij behoudt alle rechten van je systeem en er zijn geen licentiekosten",
            "1.000+ developers beschikbaar in Nederland: stap over naar een ander bureau binnen weken",
            "Hosting vrijheid: deploy waar je wilt. AWS, Azure, eigen servers of wat jij ook wilt",
            "Uniforme codebase: elke Laravel developer kan het werk probleemloos overnemen",
            "Wekelijkse security updates: continue beveiligingsupdates door de wereldwijde Laravel community",
        ],
    ],
] as const;
const reasons = [
    [
        "/assets/img/enterprise-ready.svg",
        "Enterprise-ready features",
        "Laravel biedt als enige framework een complete suite: van admin panels (Nova) tot serverless deployment (Vapor). Andere frameworks vereisen veel externe packages die niet altijd goed samenwerken.",
    ],
    [
        "/assets/img/maintenance.svg",
        "Onderhoud",
        "De uniforme Laravel codebase betekent dat elke Laravel developer het werk van een ander kan overnemen. Bij frameworks zoals Node.js (Express) of Ruby on Rails loop je risico op developer lock-in door inconsistente implementaties.",
    ],
    [
        "/assets/img/development-speed.svg",
        "Ontwikkelsnelheid",
        "Laravel wint van Symfony, Django en Ruby on Rails door zijn uitgebreide ecosysteem van kant-en-klare componenten. Waar andere frameworks maanden vergen voor basisfunctionaliteit, levert Laravel een werkend prototype binnen weken.",
    ],
] as const;

export default function FrameworkLanding({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <main className="dlf-bm dlf-rails dlf-bm--framework" data-dlf-footer-cta-stage>
                <section className="dlf-bm__head dlf-divider-section">
                    <p className="dlf-kicker">Laravel voor organisaties</p>
                    <h1>{page.title}</h1>
                    <p>
                        Laravel biedt een compleet ecosysteem dat met je business meegroeit. 150+
                        miljoen projecten wereldwijd bewijzen dat Laravel werkt voor bedrijven van
                        elke grootte.
                    </p>
                </section>
                <PhotoBand framework />
                <section
                    className="dlf-bm-objections dlf-divider-section dlf-divider-theme-inverse"
                    aria-labelledby="framework-benefits-heading"
                >
                    <div className="dlf-bm-objections__inner">
                        <header>
                            <h2 id="framework-benefits-heading">
                                De vele voordelen van Laravel voor IT-managers en CTO’s
                            </h2>
                            <p>
                                Je krijgt een uitgebreide bibliotheek van kant-en-klare
                                functionaliteiten, een actieve community van 50.000+ ontwikkelaars
                                en bewezen stabiliteit doordat er wekelijks verbeteringen
                                doorgevoerd worden.
                            </p>
                        </header>
                        <div className="dlf-bm-objections__cards">
                            {advantages.map(([title, copy, items]) => (
                                <article key={title}>
                                    <span aria-hidden="true">“</span>
                                    <h3>{title}</h3>
                                    <p>{copy}</p>
                                    <ul>
                                        {items.map((item) => (
                                            <li key={item}>{item}</li>
                                        ))}
                                    </ul>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
                <section
                    className="dlf-bm-pains dlf-bm-framework-reasons dlf-divider-section"
                    aria-labelledby="framework-reasons-heading"
                >
                    <header>
                        <h2 id="framework-reasons-heading">
                            4 redenen waarom Laravel het ‘wint’ van o.a. Symfony, Django en Ruby bij
                            een webapplicatie op maat
                        </h2>
                    </header>
                    <div className="dlf-bm-grid-2 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                        {reasons.map(([icon, title, copy]) => (
                            <article key={title}>
                                <img src={icon} width="44" height="44" alt="" />
                                <h3>{title}</h3>
                                <p>{copy}</p>
                            </article>
                        ))}
                        <article>
                            <img
                                src="/assets/img/available-developers.svg"
                                width="46"
                                height="44"
                                alt=""
                            />
                            <h3>Beschikbare developers</h3>
                            <ul>
                                {[
                                    "Laravel: 1.000+ beschikbare developers",
                                    "Symfony: 200+ developers",
                                    "Django/Python: Beperkt beschikbaar",
                                    "Ruby on Rails: Schaars",
                                    "Node.js: Schaars door hoge vraag",
                                ].map((item) => (
                                    <li key={item}>
                                        <span>{item}</span>
                                    </li>
                                ))}
                            </ul>
                        </article>
                    </div>
                </section>
                <section
                    className="dlf-bm-ink-band dlf-bm-ink-band--center dlf-divider-section dlf-divider-theme-inverse"
                    aria-labelledby="framework-conclusion-heading"
                >
                    <div className="dlf-bm-ink-band__inner">
                        <h2 id="framework-conclusion-heading">Conclusie</h2>
                        <p>
                            Laravel projecten hebben gemiddeld <strong>40%</strong> lagere
                            ontwikkelkosten en kunnen tot <strong>60%</strong> sneller van start.
                        </p>
                    </div>
                </section>
                <LandingCases
                    cases={page.support.frameworkLandingCases}
                    heading="Deze bedrijven kozen bewust om te bouwen in Laravel"
                    introduction="Van handmatige Excel-sheets naar geautomatiseerde systemen, van losse tools naar één centraal platform. Zo pakten andere organisaties hun uitdagingen aan."
                />
                <ContactSection framework />
                <FoundationClose tender />
            </main>
        </PublicPageFrame>
    );
}
