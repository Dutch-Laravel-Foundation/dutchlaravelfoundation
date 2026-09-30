import {
    ContactSection,
    ContentBlocks,
    FoundationClose,
    LandingCases,
    PhotoBand,
    PublicPageFrame,
} from "@/components/public-pages-react";
import { DlfButtonLink } from "@/components/ui/DlfButton";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };
const pains = [
    [
        "/assets/redesign/icons/pijnpunten/pijnpunt-groei.svg",
        "Je bedrijf groeit, maar je systemen groeien niet mee",
        [
            "Wat eerst werkte, wordt nu een rem op je groei",
            "Meer klanten zorgt voor meer chaos in plaats van meer omzet",
            "Medewerkers klagen over omslachtige, trage of onhandige systemen",
        ],
    ],
    [
        "/assets/redesign/icons/pijnpunten/pijnpunt-ontwikkelaar.svg",
        "Je huidige ontwikkelaar blijkt onbetrouwbaar",
        [
            "Je (freelance) ontwikkelaar is gestopt of slecht bereikbaar",
            "Doorlooptijden van onderhoud en nieuwe features duren steeds langer",
            "Concurrenten gaan sneller en je verliest momentum",
        ],
    ],
    [
        "/assets/redesign/icons/pijnpunten/pijnpunt-systeem.svg",
        "Je huidige systeem laat je in de steek",
        [
            "Je hebt regelmatig storingen of crashes die niet meer uit te leggen zijn aan klanten",
            "Je moet steeds vaker klanten compenseren, omdat systemen niet werken (faalkosten)",
            "Onderhoud wordt steeds duurder",
        ],
    ],
    [
        "/assets/redesign/icons/pijnpunten/pijnpunt-controle.svg",
        "Je verliest overzicht en controle",
        [
            "Informatie is verspreid over verschillende tools",
            "Handmatige processen kosten steeds meer tijd",
            "Er ontstaan meer fouten door o.a. onduidelijke data of haastwerk",
        ],
    ],
] as const;
const objections = [
    [
        "Een maatwerk systeem kost toch een vermogen?",
        [
            "In Laravel kun je klein starten en het systeem stap voor stap uitbreiden",
            "Laravel-ontwikkelaars gebruiken bouwblokken, waardoor kosten lager kunnen uitvallen",
            "Een Laravel-systeem is vaak goedkoper dan jarenlang licenties betalen voor standaardsoftware die niet meer bij je past",
        ],
    ],
    [
        "Maatwerk laten ontwikkelen duurt toch vreselijk lang?",
        [
            "Door slimme herbruikbare componenten werkt een ontwikkelaar 20 tot 50% sneller",
            "Een werkende eerste versie staat er vaak binnen weken in plaats van maanden",
            "Snel testen, feedback verzamelen en bijsturen naar jouw ideale systeem",
        ],
    ],
    [
        "Ik wil niet afhankelijk zijn van één ontwikkelpartij",
        [
            "Alleen Nederland heeft al honderden bureaus die Laravel gebruiken",
            "Wil je op termijn wisselen van ontwikkelaar? Een ander pakt het probleemloos over",
            "Jij bent en blijft eigenaar van je systeem en data",
        ],
    ],
] as const;
const reasons = [
    [
        "/assets/redesign/illustrations/proven-secure.svg",
        "Bewezen en betrouwbaar",
        "Zo'n 150+ miljoen projecten wereldwijd zijn ontwikkeld in Laravel. Zowel kleine start-ups als grote enterprises vertrouwen op deze technologie.",
    ],
    [
        "/assets/redesign/illustrations/boosting-knowledge.svg",
        "Wereldwijde community",
        "Honderdduizenden ontwikkelaars wereldwijd verbeteren Laravel continu en delen nieuwe oplossingen. Jouw ontwikkelaar profiteert van de collectieve kennis.",
    ],
    [
        "/assets/redesign/illustrations/future-proof.svg",
        "100% toekomstbestendig",
        "Laravel evolueert mee met nieuwe technologieën en beveiligingsstandaarden. Jouw systeem blijft dus niet achter, maar groeit mee.",
    ],
] as const;

export default function GeneralLanding({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <div className="dlf-rails">
                <ContentBlocks
                    blocks={page.content.filter((block) => block.type.startsWith("dlf_"))}
                    railLevel
                />
            </div>
            <main className="dlf-bm dlf-rails" data-dlf-footer-cta-stage>
                <section className="dlf-bm__head dlf-divider-section">
                    <p className="dlf-kicker">Betaalbaar maatwerk</p>
                    <h1>Een eigen systeem laten bouwen is betaalbaarder dan je denkt</h1>
                    <p>
                        Veel organisaties wachten tot het piept en kraakt, hoewel dat misschien niet
                        de slimste tactiek is. Dan slaat de realiteit toe: dit MOET anders.
                    </p>
                </section>
                <section
                    className="dlf-bm-pains dlf-divider-section"
                    aria-labelledby="pain-heading"
                >
                    <header>
                        <h2 id="pain-heading">Herken jij ook deze pijnpunten in je bedrijf?</h2>
                    </header>
                    <div className="dlf-bm-grid-2 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                        {pains.map(([icon, title, items]) => (
                            <article key={title}>
                                <img src={icon} width="46" height="46" alt="" />
                                <h3>{title}</h3>
                                <ul>
                                    {items.map((item) => (
                                        <li key={item}>{item}</li>
                                    ))}
                                </ul>
                            </article>
                        ))}
                    </div>
                </section>
                <PhotoBand />
                <section className="dlf-bm-split dlf-bm-doubts dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed">
                    <div>
                        <h2>Maatwerk klinkt duur en ingewikkeld, maar dat hóeft niet!</h2>
                        <p>
                            We begrijpen je twijfels. Je bent niet de eerste ondernemer die bij
                            maatwerksoftware denkt aan oneindige projecten, enorme kosten en
                            technische complexiteit. Maar die tijd is voorbij dankzij Laravel, een
                            van de meest geschikte open source frameworks voor een systeem op maat.
                        </p>
                    </div>
                    <figure data-progressive-media-frame>
                        <img
                            src="/assets/redesign/photos/laravel-ecosystem.png"
                            alt="Het Laravel-ecosysteem"
                        />
                    </figure>
                </section>
                <section className="dlf-bm-objections dlf-divider-section dlf-divider-theme-inverse">
                    <div className="dlf-bm-objections__inner">
                        <header>
                            <h2>3 veelgehoorde bezwaren tegen maatwerk</h2>
                            <p>En hoe een systeem in Laravel de oplossing kan zijn</p>
                            <div
                                className="dlf-bm-objections__mark dlf-bm-objections__mark--bubble"
                                aria-hidden="true"
                            >
                                <img
                                    src="/assets/img/text-bubble.svg"
                                    width="541"
                                    height="484"
                                    alt=""
                                />
                            </div>
                        </header>
                        <div className="dlf-bm-objections__cards">
                            {objections.map(([title, items]) => (
                                <article key={title}>
                                    <span aria-hidden="true">“</span>
                                    <h3>{title}</h3>
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
                    className="dlf-bm-laravel dlf-divider-section"
                    aria-labelledby="laravel-landing-heading"
                >
                    <div className="dlf-bm-split dlf-divider-split dlf-divider-split--stacked-reversed">
                        <div>
                            <h2 id="laravel-landing-heading">
                                Waarom het Laravel-framework de slimste keuze is voor jouw maatwerk
                                systeem
                            </h2>
                            <p>
                                Laravel is niet zomaar een techniek. Het is dé reden waarom moderne
                                maatwerksoftware nu wél haalbaar en betaalbaar is geworden. De crux
                                zit in de slimme aanpak van dit framework dat 100% futureproof is.
                            </p>
                            <DlfButtonLink href="/wat-is-laravel" face="red" shadow="red">
                                Meer over Laravel
                            </DlfButtonLink>
                        </div>
                        <figure data-progressive-media-frame>
                            <img src="/assets/redesign/photos/laravel-12.png" alt="Laravel 12" />
                        </figure>
                    </div>
                    <div className="dlf-bm-grid-3 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        {reasons.map(([icon, title, copy]) => (
                            <article key={title}>
                                <div>
                                    <img src={icon} width="44" height="44" alt="" />
                                </div>
                                <h3>{title}</h3>
                                <p>{copy}</p>
                            </article>
                        ))}
                    </div>
                </section>
                <LandingCases
                    cases={page.support.generalLandingCases}
                    heading="Laravel als bewuste keuze voor andere MKB(+) ondernemers en software start-ups"
                    introduction="Deze voorbeelden tonen concrete Laravel-oplossingen die andere ondernemers al succesvol gebruiken."
                />
                <ContactSection />
                <FoundationClose />
            </main>
        </PublicPageFrame>
    );
}
