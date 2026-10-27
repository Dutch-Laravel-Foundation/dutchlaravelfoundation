import { LaravelMark, ProgressiveImage, PublicPageFrame } from "@/components/public-pages-react";
import { DlfButtonLink } from "@/components/ui/DlfButton";

type Props = {
    page: App.Data.PublicPages.PublicPageData;
    site: App.Data.SiteShell.SiteShellData;
};
const offers = [
    ["/assets/img/enterprise-ready.svg", "Een heldere structuur die studenten snel houvast geeft"],
    [
        "/assets/img/maintenance.svg",
        "Een compleet ecosysteem voor databases, API’s, authenticatie, testing en deployment",
    ],
    [
        "/assets/img/development-speed.svg",
        "Snel zichtbare resultaten, zonder dat de onderliggende principes verdwijnen",
    ],
    [
        "/assets/img/available-developers.svg",
        "Een natuurlijke verbinding tussen techniek, samenwerking en softwarekwaliteit",
    ],
    [
        "/assets/img/community-outline.svg",
        "Een actieve internationale community en uitgebreide documentatie",
    ],
    [
        "/assets/img/aanbesteding/icon-scale.png",
        "Een leeromgeving die kan meegroeien van mbo tot en met wo",
    ],
] as const;
const skills = [
    [
        "Technisch vakmanschap",
        [
            "MVC, routing, controllers en databases",
            "Authenticatie, autorisatie en API’s",
            "Geautomatiseerd testen en codekwaliteit",
            "Security, privacy en AVG",
            "Deployment en developer tooling",
            "AI-integratie en slimme functies",
        ],
    ],
    [
        "Professioneel handelen",
        [
            "Werken met user stories en requirements",
            "Samenwerken met Git en pull requests",
            "Code reviews en feedback verwerken",
            "Documenteren en keuzes onderbouwen",
            "Samenwerken in multidisciplinaire teams",
            "AI kritisch en verantwoord inzetten",
        ],
    ],
] as const;
const levels = [
    [
        "Mbo: beroepsgericht, zichtbaar en stapsgewijs",
        "Binnen mbo niveau 4 Software Developer helpt Laravel studenten om snel samenhang te zien tussen code, database, gebruikersinterface en beroepspraktijk. Praktijkgerichte opdrachten, herhaling en duidelijke tussenproducten maken abstracte concepten tastbaar.",
        [
            "Werken aan herkenbare beroepsproducten en realistische klantvragen",
            "Basisvaardigheden PHP en objectgeoriënteerd programmeren toepassen in een groter geheel",
            "Leren samenwerken, testen, documenteren en opleveren",
            "Voorbereiden op stage, werk en verdere ontwikkeling",
            "Leeruitkomsten verbinden aan kerntaken, werkprocessen en beroepshouding",
        ],
    ],
    [
        "Hbo: verdiepen, ontwerpen en onderbouwen",
        "Binnen hbo-ICT en Software Engineering kan Laravel dienen als praktisch fundament voor complexere ontwerp- en onderzoeksvragen. Studenten kunnen bijvoorbeeld architectuurkeuzes vergelijken, API’s ontwerpen, teststrategieën opzetten en softwarekwaliteit onderbouwen.",
        [
            "Softwarearchitectuur en design patterns",
            "API-design en integraties",
            "Security en privacy by design",
            "DevOps, cloud en deployment",
            "Schaalbaarheid en onderhoudbaarheid",
            "Onderzoekend en evidence-informed ontwerpen",
        ],
    ],
    [
        "Wo: experimenteren en onderzoeken",
        "Binnen universitaire opleidingen kan Laravel worden ingezet als experimenteeromgeving voor software engineering, webarchitecturen, mens-computerinteractie, AI-integraties en onderzoek naar ontwikkelprocessen. Het framework maakt het mogelijk om theoretische modellen snel te toetsen in een werkend systeem.",
        [
            "Prototypes bouwen om theoretische modellen te toetsen",
            "Webarchitecturen en software-engineeringpraktijken vergelijken",
            "Gebruikersonderzoek en mens-computerinteractie in een werkend systeem",
            "Experimenteren met AI-integraties en agents",
            "Onderzoek naar ontwikkelprocessen, codekwaliteit en samenwerking",
        ],
    ],
] as const;
const roles = [
    [
        "Stichting Praktijkleren",
        "Kent het mbo, de kwalificatiedossiers en de praktijk van beroepsgericht opleiden. SPL vertaalt technische praktijkcases naar bruikbare leeractiviteiten, beroepsproducten en beoordelingssituaties die passen bij de opleiding.",
        [
            "Kennis van beroepsonderwijs en kwalificatiedossiers",
            "Vertaling naar leerdoelen, opdrachten en beoordeling",
            "Verbinding met scholen, docenten en onderwijsontwikkeling",
            "Borging van beroepsgericht en haalbaar onderwijs",
        ],
    ],
    [
        "Dutch Laravel Foundation",
        "Brengt actuele praktijkkennis en een professioneel netwerk van developers en Laravel-bedrijven.",
        [
            "Actuele kennis van Laravel en het ecosysteem",
            "Verbinding met developers, bedrijven en community",
            "Gastcolleges, praktijkcases, events en code reviews",
            "Stages, bedrijfsbezoeken en technische inspiratie",
        ],
    ],
] as const;
const activities = [
    "Ontwikkelen van praktijkgerichte Laravel-opdrachten voor mbo en hbo",
    "Koppelen van cases aan kerntaken, werkprocessen en leeruitkomsten",
    "Docentprofessionalisering en inspiratiesessies",
    "Gastcolleges, hackathons en projectweken",
    "Code reviews en feedback door developers uit het werkveld",
    "Een stagebank en verbinding met Laravel-bedrijven",
    "Delen van leermaterialen en voorbeelden uit opleidingen",
] as const;
const phases = [
    ["Basis", "PHP, HTML, CSS, SQL en objectgeoriënteerd programmeren"],
    ["Structuur", "Composer, dependency management en MVC"],
    ["Webapplicatie", "Routing, controllers, Blade en validatie"],
    ["Data", "Migrations, Eloquent en databases ontwerpen"],
    ["Gebruikers", "Authenticatie, autorisatie en rollen"],
    ["Kwaliteit", "Feature tests, security en code reviews"],
    ["Integratie", "REST API’s, frontend of mobiele toepassingen"],
    ["Professionalisering", "Deployment, documentatie en projectmatig werken"],
    ["Agentic engineering", "Agents, skills, MCP servers en guardrails"],
] as const;
const teacherSupport = [
    "Een heldere startset met leerdoelen, voorbeeldprojecten en docenthandleiding",
    "Kant-en-klare opdrachten die eenvoudig aangepast kunnen worden",
    "Praktijkcases met verschillende moeilijkheidsniveaus",
    "Gastlessen en code reviews door professionals",
    "Voorbeelden van toetsing, feedback en beroepsproducten",
    "Een community waarin docenten ervaringen en materialen uitwisselen",
    "Aandacht voor eigen professionalisering van docenten",
] as const;
const habitIcons = {
    git: (
        <>
            <circle cx="6" cy="5" r="2.5" />
            <circle cx="6" cy="19" r="2.5" />
            <circle cx="18" cy="8" r="2.5" />
            <path d="M6 7.5v9M18 10.5c0 4-6 3-11 6.5" />
        </>
    ),
    review: (
        <>
            <circle cx="6" cy="5" r="2.5" />
            <circle cx="6" cy="19" r="2.5" />
            <circle cx="18" cy="19" r="2.5" />
            <path d="M6 7.5v9M18 16.5V9a3 3 0 0 0-3-3h-4M13 3.5 10.5 6 13 8.5" />
        </>
    ),
    test: (
        <>
            <rect x="3.5" y="3.5" width="17" height="17" rx="1.5" />
            <path d="m8 12 3 3 5-6" />
        </>
    ),
    issue: (
        <>
            <path d="M9 6h11M9 12h11M9 18h11" />
            <path d="m3.5 6 1.25 1.25L7 5M3.5 12l1.25 1.25L7 11" />
            <circle cx="5" cy="18" r="1.5" />
        </>
    ),
    docs: (
        <>
            <path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8z" />
            <path d="M14 3v5h5M8.5 13h7M8.5 17h7" />
        </>
    ),
    security: (
        <>
            <path d="M12 3 4.5 6v5.5c0 4.5 3.2 8 7.5 9.5 4.3-1.5 7.5-5 7.5-9.5V6z" />
            <path d="m9 12 2.25 2.25L15.5 10" />
        </>
    ),
    iterate: (
        <>
            <path d="M20 12a8 8 0 0 1-14.3 4.9M4 12a8 8 0 0 1 14.3-4.9" />
            <path d="M18.5 3v4.5H14M5.5 21v-4.5H10" />
        </>
    ),
};
const habits = [
    ["git", "Versiebeheer met Git"],
    ["review", "Pull requests en code reviews"],
    ["test", "Automatisch testen"],
    ["issue", "Issue tracking en user stories"],
    ["docs", "Duidelijke documentatie"],
    ["security", "Security awareness"],
    ["iterate", "Iteratief verbeteren en opleveren"],
] as const;

function Photo({ src, alt = "" }: { src: string; alt?: string }) {
    return (
        <ProgressiveImage
            src={src}
            alt={alt}
            width="1200"
            height="800"
            loading="lazy"
            decoding="async"
        />
    );
}

export default function EducationLanding({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <main className="dlf-bm dlf-rails dlf-bm--education" data-dlf-footer-cta-stage>
                <section className="dlf-bm__head dlf-divider-section">
                    <p className="dlf-kicker">Laravel in het onderwijs</p>
                    <h1>{page.title}</h1>
                    <p>
                        Voor docenten en onderwijsontwikkelaars in mbo, hbo en wo. Actueel onderwijs
                        ontstaat waar didactiek, beroepspraktijk en community elkaar versterken. De
                        Dutch Laravel Foundation brengt de actuele softwarepraktijk in.
                    </p>
                </section>
                <section className="dlf-bm-split dlf-divider-section dlf-divider-section--yield-to-next dlf-divider-split dlf-divider-split--stacked-reversed">
                    <div>
                        <h2>Softwareonderwijs dat meebeweegt met de praktijk</h2>
                        <p>
                            Softwareontwikkeling verandert snel. Werkgevers zoeken startende
                            developers die meer kunnen dan code schrijven. Zij moeten kunnen
                            samenwerken, ontwerpen, testen, documenteren, beveiligen, onderhouden en
                            verantwoord omgaan met AI.
                        </p>
                        <p>
                            Als docent wil je studenten daarom niet alleen programmeersyntax
                            aanleren, maar hen laten ervaren hoe professionele softwareontwikkeling
                            werkt. Laravel biedt daarvoor een toegankelijke, complete en
                            praktijkgerichte leeromgeving.
                        </p>
                    </div>
                    <figure data-progressive-media-frame>
                        <Photo
                            src="/assets/redesign/photos/larafest-groepsfoto-1280.webp"
                            alt="Laravel-developers bij een community-event"
                        />
                    </figure>
                </section>
                <section
                    className="dlf-bm-ink-band dlf-bm-ink-band--center dlf-bm-ink-band--red dlf-divider-section dlf-divider-section--full-bleed-top dlf-divider-theme-inverse"
                    aria-label="Laravel als middel"
                >
                    <div className="dlf-bm-ink-band__inner">
                        <p>
                            Laravel is een middel waarmee studenten leren software ontwikkelen zoals
                            professionals dat dagelijks doen.
                        </p>
                    </div>
                </section>
                <section
                    className="dlf-bm-pains dlf-bm-framework-reasons dlf-divider-section"
                    aria-labelledby="education-offers-heading"
                >
                    <header>
                        <h2 id="education-offers-heading">Wat biedt Laravel in het onderwijs?</h2>
                        <p>
                            Met Laravel kunnen studenten werken aan realistische toepassingen: een
                            stageportaal, reserveringssysteem, dashboard, ledenadministratie,
                            webshop, mobiele API of AI-assistent. Daarbij denken zij na over
                            gebruikers, rechten, onderhoudbaarheid, privacy, security en
                            schaalbaarheid.
                        </p>
                    </header>
                    <div className="dlf-bm-grid-3 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                        {offers.map(([icon, offer]) => (
                            <article key={offer}>
                                <img src={icon} width="44" height="44" alt="" />
                                <h3>{offer}</h3>
                            </article>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-bm-pains dlf-bm-framework-reasons dlf-divider-section"
                    aria-labelledby="education-skills-heading"
                >
                    <header>
                        <h2 id="education-skills-heading">Wat studenten leren</h2>
                        <p>
                            Laravel brengt technische en professionele vaardigheden samen binnen één
                            projectomgeving.
                        </p>
                    </header>
                    <div className="dlf-bm-grid-2 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                        {skills.map(([title, items]) => (
                            <article key={title}>
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
                <section
                    className="dlf-bm-ink-band dlf-bm-ink-band--center dlf-bm-ink-band--wide dlf-divider-section dlf-divider-theme-inverse"
                    aria-labelledby="education-didactic-heading"
                >
                    <div className="dlf-bm-ink-band__inner">
                        <h2 id="education-didactic-heading">Didactische winst</h2>
                        <p>
                            Met Laravel kunnen studenten snel een werkend product bouwen en
                            bespreken waarom bepaalde ontwerpkeuzes verstandig zijn.
                        </p>
                    </div>
                </section>
                <section
                    className="dlf-bm-split dlf-divider-section dlf-divider-section--yield-to-next dlf-divider-split dlf-divider-split--stacked-reversed"
                    aria-labelledby="education-agentic-heading"
                >
                    <div>
                        <p className="dlf-kicker">Agentic engineering</p>
                        <h2 id="education-agentic-heading">
                            Laravel als fundament voor agentic engineering
                        </h2>
                        <p>
                            Laravel is bij uitstek geschikt als basis voor agentic engineering,
                            juist omdat het een opinionated framework is. Het biedt voor veel
                            voorkomende ontwikkelvraagstukken een duidelijke, doordachte en
                            herkenbare manier van werken. Routing, validatie, authenticatie,
                            autorisatie, databases, queues en testing volgen vaste conventies en
                            patronen.
                        </p>
                        <p>
                            Die voorspelbaarheid is waardevol wanneer developers steeds vaker
                            samenwerken met AI-agents. Een agent hoeft niet voor ieder project
                            opnieuw te bepalen hoe een route wordt opgebouwd, waar validatie
                            thuishoort of hoe data wordt benaderd. Het framework biedt hiervoor
                            duidelijke conventies en bestaande abstracties. Daardoor kunnen agents
                            binnen herkenbare kaders code genereren, aanpassen, testen en
                            verbeteren.
                        </p>
                        <p>
                            Voor studenten is dat bovendien leerzaam: zij leren niet alleen wat code
                            doet, maar ook werken binnen een professionele set van conventies en
                            ontwerpkeuzes. De vraag verschuift daarmee van “hoe schrijf ik deze
                            code?” naar “welke oplossing past binnen de architectuur en conventies
                            van het framework?” Dat sluit direct aan op de manier waarop
                            softwareontwikkeling zich ontwikkelt richting agentic engineering.
                        </p>
                    </div>
                    <figure data-progressive-media-frame>
                        <Photo
                            src="/assets/redesign/photos/developer-bureau-1200.webp"
                            alt="Developer aan het werk achter een bureau"
                        />
                    </figure>
                </section>
                <section
                    className="dlf-bm-objections dlf-divider-section dlf-divider-section--full-bleed-top dlf-divider-theme-inverse"
                    aria-labelledby="education-levels-heading"
                >
                    <div className="dlf-bm-objections__inner">
                        <header>
                            <h2 id="education-levels-heading">
                                Eén framework, verschillende onderwijsniveaus
                            </h2>
                            <p>
                                De kracht van Laravel zit niet in een vaste leerlijn. Het framework
                                kan op verschillende niveaus een andere didactische functie krijgen.
                            </p>
                            <LaravelMark />
                        </header>
                        <div className="dlf-bm-objections__cards">
                            {levels.map(([title, copy, items]) => (
                                <article key={title}>
                                    <h3>{title}</h3>
                                    <p>{copy}</p>
                                    {items.length ? (
                                        <>
                                            <p>Laravel helpt met:</p>
                                            <ul>
                                                {items.map((item) => (
                                                    <li key={item}>{item}</li>
                                                ))}
                                            </ul>
                                        </>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
                <section
                    className="dlf-bm-pains dlf-bm-framework-reasons dlf-bm-partnership dlf-divider-section"
                    aria-labelledby="education-partnership-heading"
                >
                    <header>
                        <p className="dlf-kicker">Voor het mbo</p>
                        <h2 id="education-partnership-heading">
                            Samenwerking met Stichting Praktijkleren
                        </h2>
                        <p>
                            Goed softwareonderwijs ontstaat waar onderwijs, bedrijfsleven en
                            community elkaar ontmoeten. Stichting Praktijkleren en de Dutch Laravel
                            Foundation hebben daarin ieder een eigen, aanvullende rol.
                        </p>
                    </header>
                    <div className="dlf-bm-grid-2 dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                        {roles.map(([title, copy, items]) => (
                            <article key={title}>
                                <p className="dlf-kicker">De rol van</p>
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
                    <div className="dlf-bm-partnership__together dlf-divider-region dlf-divider-theme-inverse">
                        <div>
                            <h3>Samen maken we de vertaalslag</h3>
                            <p>
                                SPL helpt de kennis van de DLF om te zetten in onderwijs dat past
                                bij niveau, curriculum, leerdoelen en beroepspraktijk. Zo ontstaat
                                ondersteuning die technisch sterk én onderwijsbaar is.
                            </p>
                        </div>
                        <div>
                            <p>Mogelijke gezamenlijke activiteiten:</p>
                            <ul>
                                {activities.map((item) => (
                                    <li key={item}>{item}</li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </section>
                <section className="dlf-bm-split dlf-divider-section dlf-divider-section--yield-to-next dlf-divider-split dlf-divider-split--stacked-reversed">
                    <div>
                        <p className="dlf-kicker">Voor studenten</p>
                        <h2>Kickstart met Laravel</h2>
                        <p>
                            Op de pagina Kickstart met Laravel van Stichting Praktijkleren vinden
                            studenten met een leermiddelenlicentie een cursus Laravel met 27
                            praktijkopdrachten, voorbereiding op de officiële Laravel Student
                            Developer Certification, oefenmogelijkheden, links naar gastcolleges,
                            een stagebank en praktische tips van ervaren developers.
                        </p>
                        <p>
                            Voor mbo-studenten is certificering een mooie manier om hun kennis
                            zichtbaar te maken voor werkgevers.
                        </p>
                    </div>
                    <figure data-progressive-media-frame>
                        <Photo
                            src="/assets/redesign/photos/kantoor-developers-1280.webp"
                            alt="Developers aan het werk op kantoor"
                        />
                    </figure>
                </section>
                <section
                    className="dlf-bm-learning-path dlf-divider-section dlf-divider-section--full-bleed-top"
                    aria-labelledby="education-path-heading"
                >
                    <div className="dlf-bm-learning-path__grid dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        <div className="dlf-bm-learning-path__intro">
                            <div>
                                <p className="dlf-kicker">Leerlijn</p>
                                <h2 id="education-path-heading">Een mogelijke leerlijn</h2>
                                <p>
                                    Studenten hoeven niet alles tegelijk te beheersen. Door meerdere
                                    projecten uit te voeren herkennen zij geleidelijk de structuur,
                                    conventies en professionele werkwijzen.
                                </p>
                                <div className="dlf-bm-learning-path__note">
                                    <h3>Begin klein</h3>
                                    <p>
                                        Kies één betekenisvol project, laat studenten vroeg
                                        samenwerken via Git en maak testen en documentatie vanaf het
                                        begin zichtbaar. Breid daarna stap voor stap uit.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <ol className="dlf-bm-learning-path__steps dlf-divider-list">
                            {phases.map(([title, focus], index) => (
                                <li key={title}>
                                    <span aria-hidden="true">
                                        {String(index + 1).padStart(2, "0")}
                                    </span>
                                    <div>
                                        <h3>
                                            <span className="sr-only">Fase {index + 1}: </span>
                                            {title}
                                        </h3>
                                        <p>{focus}</p>
                                    </div>
                                </li>
                            ))}
                        </ol>
                    </div>
                </section>
                <section className="dlf-bm-split dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed">
                    <div>
                        <h2>Voor docenten: houd het haalbaar en actueel</h2>
                        <p>
                            Een nieuw framework opnemen in het curriculum vraagt tijd. Daarom is het
                            belangrijk dat ondersteuning niet alleen technisch is, maar ook
                            didactisch en organisatorisch.
                        </p>
                        <h3>Wat helpt?</h3>
                        <ul>
                            {teacherSupport.map((item) => (
                                <li key={item}>{item}</li>
                            ))}
                        </ul>
                    </div>
                    <figure data-progressive-media-frame>
                        <Photo
                            src="/assets/redesign/photos/easi-2025.jpg"
                            alt="Developers in gesprek tijdens een event"
                        />
                    </figure>
                </section>
                <section
                    className="dlf-bm-ink-band dlf-divider-section dlf-divider-theme-inverse"
                    aria-labelledby="education-ai-heading"
                >
                    <div className="dlf-bm-ink-band__inner">
                        <h2 id="education-ai-heading">
                            AI: hulpmiddel, geen vervanging van begrip
                        </h2>
                        <p>
                            AI speelt een steeds grotere rol in softwareontwikkeling. Binnen
                            Laravel-projecten kunnen studenten experimenteren met code-assistentie,
                            debugging, testgeneratie, chatbots, slimme zoekfuncties en
                            tekstclassificatie. Tegelijk blijft de onderwijsvraag centraal: kan de
                            student uitleggen, beoordelen en verbeteren wat de AI oplevert? Juist de
                            duidelijke conventies van Laravel maken het mogelijk om AI niet alleen
                            als code-assistent, maar steeds meer als ontwikkelpartner en agent in te
                            zetten binnen herkenbare en beheersbare kaders.
                        </p>
                        <p className="dlf-bm-ink-band__lead">
                            AI ondersteunt het ontwikkelproces, maar de student blijft{" "}
                            <strong>verantwoordelijk</strong> voor begrip, kwaliteit, veiligheid en
                            keuzes.
                        </p>
                    </div>
                </section>
                <section
                    className="dlf-bm-pains dlf-bm-framework-reasons dlf-bm-habits dlf-divider-section"
                    aria-labelledby="education-habits-heading"
                >
                    <header>
                        <h2 id="education-habits-heading">
                            Werk met dezelfde gewoonten als professionals
                        </h2>
                    </header>
                    <div className="dlf-fill-grid dlf-divider-region dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                        {habits.map(([icon, habit]) => (
                            <article key={habit}>
                                <svg
                                    viewBox="0 0 24 24"
                                    width="28"
                                    height="28"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.5"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    aria-hidden="true"
                                >
                                    {habitIcons[icon]}
                                </svg>
                                <h3>{habit}</h3>
                            </article>
                        ))}
                        <span className="dlf-divider-grid__filler" aria-hidden="true" />
                    </div>
                </section>
                <section
                    className="dlf-bm-narrative dlf-bm-narrative--center dlf-divider-section"
                    aria-labelledby="education-generation-heading"
                >
                    <div className="dlf-bm-narrative__inner">
                        <h2 id="education-generation-heading">
                            Samen bouwen aan de volgende generatie developers
                        </h2>
                        <p>
                            De vraag is niet alleen welk framework studenten leren. De belangrijkste
                            vraag is welke ontwikkelaars we willen opleiden: professionals die
                            begrijpen wat zij bouwen, samenwerken met anderen, kwaliteit bewaken en
                            blijven leren. Laravel biedt een krachtige omgeving om dat vakmanschap
                            te oefenen. De Dutch Laravel Foundation brengt de softwarepraktijk
                            dichtbij.
                        </p>
                    </div>
                </section>
                <section className="dlf-bm-efficient dlf-divider-section">
                    <div className="dlf-bm-split dlf-divider-split dlf-divider-split--stacked-reversed">
                        <div>
                            <h2>Kennismaken of samenwerken?</h2>
                            <p>
                                Goed softwareonderwijs ontstaat door samenwerking tussen onderwijs,
                                bedrijfsleven en community. Wil je Laravel een plek geven binnen
                                jouw opleiding? Zoek je een praktijkcase, gastcollege, projectweek
                                of een passende leerlijn? De Dutch Laravel Foundation denkt graag
                                mee over een aanpak die past bij jouw studenten, docententeam en
                                onderwijsdoelen.
                            </p>
                            <p>
                                Voor mbo bundelen Stichting Praktijkleren en de Dutch Laravel
                                Foundation hun expertise, zodat technische vakkennis,
                                praktijkgericht leren en de eisen van het kwalificatiedossier
                                samenkomen in inspirerend en toekomstgericht softwareonderwijs.
                            </p>
                        </div>
                        <figure data-progressive-media-frame>
                            <Photo
                                src="/assets/redesign/photos/dlf-larafest-talk.png"
                                alt="Een spreker op Larafest"
                            />
                        </figure>
                    </div>
                    <div className="dlf-bm-grid-2 dlf-bm-efficient__actions dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        <article>
                            <div className="dlf-bm-efficient__icon" aria-hidden="true">
                                <img
                                    src="/assets/img/question-mark.svg?v=red2"
                                    width="22"
                                    height="22"
                                    alt=""
                                />
                            </div>
                            <h3>
                                Neem vrijblijvend contact op en ontdek hoe we samen studenten kunnen
                                voorbereiden op de toekomst van softwareontwikkeling.
                            </h3>
                            <DlfButtonLink href="/contact" face="red" shadow="red">
                                Neem contact op
                            </DlfButtonLink>
                        </article>
                        <article>
                            <div className="dlf-bm-efficient__icon" aria-hidden="true">
                                <img
                                    src="/assets/img/magnifier.svg?v=red2"
                                    width="22"
                                    height="22"
                                    alt=""
                                />
                            </div>
                            <h3>Zoek je een gastcollege van een ervaren Laravel-developer?</h3>
                            <DlfButtonLink
                                href="/gastcolleges-voor-hbo-mbo"
                                face="outline-red"
                                shadow="red"
                            >
                                Bekijk gastcolleges
                            </DlfButtonLink>
                        </article>
                    </div>
                </section>
            </main>
        </PublicPageFrame>
    );
}
