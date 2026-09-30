import { Button } from "@base-ui/react/button";
import { useState } from "react";

import { FoundationClose, PublicPageFrame } from "@/components/public-pages-react";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };
const requirements = [
    [
        "Laravel is open source",
        "Veel aanbestedingen vragen expliciet om open source software. Laravel voldoet daaraan: de broncode is voor iedereen toegankelijk. Dit past binnen het “Open source, tenzij” beleid van de overheid, omdat het transparantie en controle mogelijk maakt.",
    ],
    [
        "Géén vendor lock-in",
        "Omdat het Laravel framework wereldwijd wordt gebruikt volgens vaste standaarden (PSR-12 coding style guide / Common Ground), kunnen andere developers het werk overnemen. In Nederland zijn honderden partijen die Laravel gebruiken. Je zit dus nooit vast aan één leverancier (vendor lock-in).",
    ],
    [
        "Bewezen en toekomstbestendig",
        "Laravel is een PHP-framework en bestaat sinds 2011. Het evolueert mee met nieuwe technologieën en beveiligingsstandaarden. Je hoeft je dus geen zorgen te maken over verouderde software die over een paar jaar vervangen moet worden. Laravel is een stabiele basis omdat het actief wordt doorontwikkeld.",
    ],
    [
        "Veiligheid is gewaarborgd",
        "Laravel biedt bescherming tegen veelvoorkomende beveiligingsrisico’s zoals SQL-injectie, CSRF-aanvallen en XSS. Ook houdt de wereldwijde Laravel-community de veiligheid scherp in de gaten. Als er een risico wordt ontdekt, is er vaak binnen uren een oplossing beschikbaar.",
    ],
] as const;
const features = [
    [
        "/assets/img/aanbesteding/icon-demo.png",
        "Snel een werkende demo",
        "Met tools zoals Laravel Filament kunnen developers snel professionele dashboards, formulieren en gebruikersbeheer bouwen. Denk ook aan overzichten, rapportages en workflows. Als klant zie je dus al vroeg in het proces een werkende eerste versie (MVP).",
    ],
    [
        "/assets/img/aanbesteding/icon-link.png",
        "Veel kant-en-klare packages voor koppelingen (API’s)",
        "Veel (overheids)systemen moeten gekoppeld worden met andere systemen, zoals DigiD-koppelingen, zaaksystemen of boekhoudpakketten. Developers kunnen in Laravel dit soort integraties relatief eenvoudig realiseren. En vaak zijn er al bestaande modules voor beschikbaar.",
    ],
    [
        "/assets/img/aanbesteding/icon-scale.png",
        "Flexibel en schaalbaar systeem",
        "Laravel-systemen groeien moeiteloos mee met je organisatie dankzij de bouwblokken voor functionaliteiten. Ook qua gebruikersaantallen schaalt Laravel goed. Of het nu gaat om 500 of 500.000 gebruikers: het framework kan het aan. Dat heeft zich in de 150+ miljoen wereldwijde projecten al bewezen.",
    ],
] as const;
const audiences = [
    [
        "Voor opstellers/aanbieders van een aanbesteding",
        "Wil je voorkomen dat je na afloop vastzit aan één leverancier of een systeem dat niet meegroeit? Formuleer je uitvraag dan niet als een keuze tussen een standaardpakket (Buy) of volledig maatwerk (Make).",
        [
            "Vraag om toelichting waarom inschrijvers voor een specifiek open framework kiezen in plaats van er zelf één te verplichten",
            "Benoem expliciet dat een aanpak met standaard- en maatwerk modules tot de opties behoort",
            "Vraag naar het percentage maatwerk versus bestaande modules",
        ],
        "/assets/uploads/cases/diabetes-homepage-mockup-landscape_png.webp",
    ],
    [
        "Voor beoordelaars van de aanbesteding",
        "Krijg je een offerte binnen waarin Laravel wordt voorgesteld? Stel dan deze vragen aan het bureau:",
        [
            "Welke modules en componenten hebben jullie al op de plank liggen?",
            "Bij welke vergelijkbare organisaties draaien deze al?",
            "Wordt het intellectueel eigendom overgedragen aan ons als opdrachtgever?",
            "Welke PSR-standaarden hanteren jullie?",
            "Zijn jullie bekend met Common Ground (bij gemeenten)?",
        ],
        "/assets/uploads/assets/avia-mobile.png",
    ],
    [
        "Voor tender inschrijvers",
        "Vind je Laravel het beste platform voor de ontwikkeling van het (maatwerk)systeem? Leg dan in begrijpelijke taal uit waarom Laravel voldoet aan de eisen die gesteld zijn in de aanbesteding. Dit kun je doen op basis van de informatie op deze webpagina.",
        [],
        "/assets/uploads/cases/image-1.jpg",
    ],
] as const;
const faqs = [
    [
        "Is (gedeeltelijk) maatwerk niet altijd duurder dan standaardsoftware?",
        "Standaardsoftware lijkt op het eerste gezicht goedkoper, maar brengt verborgen kosten met zich mee: maandelijkse licenties, aanpassingen omdat het systeem nooit helemaal past bij jouw werkwijze en afhankelijkheid van de keuzes en ontwikkelrichting van de leverancier. Je bent nooit de eigenaar, dus je hebt geen volledige controle over je systeem en data.\n\nMet Laravel betaal je vooral aan het begin. Daarna zijn de kosten beperkt tot onderhoud en hosting, zonder doorlopende licenties. En wat je laat bouwen is van jou en past precies bij jouw situatie en werkwijze. Je bepaalt je eigen roadmap, bent niet afhankelijk van wat een leverancier wel of niet doorontwikkelt én je kunt altijd overstappen naar een andere partij.",
    ],
    [
        "Laravel is een PHP-framework. Is PHP niet ouderwets?",
        "PHP draait al jaren een groot deel van het web en wordt nog altijd actief doorontwikkeld. Moderne PHP is snel, typeveilig en uitstekend geschikt voor enterprise toepassingen. Laravel is bovendien een van de meest moderne en best onderhouden frameworks in de PHP-wereld.",
    ],
    [
        "Hoe weet ik of een Laravel-bureau betrouwbaar is?",
        "De Dutch Laravel Foundation stimuleert haar leden om te voldoen aan professionele standaarden zoals PSR-12 en het delen van best practices. Bekijk de lijst met aangesloten leden en vraag bij een bureau referenties op van vergelijkbare projecten en organisaties.",
    ],
    [
        "Ben ik vrij in de keuze van een hostingpartij bij het gebruik van Laravel?",
        "Ja. Laravel-systemen draaien op vrijwel elke moderne hostingomgeving: van Nederlandse hostingpartijen tot AWS, Azure, Google Cloud of je eigen servers. Je bent dus nooit afhankelijk van één hostingleverancier.",
    ],
] as const;

function Faq() {
    const [open, setOpen] = useState(0);
    return (
        <section className="dlf-bm-faq dlf-divider-section" aria-labelledby="tender-faq-heading">
            <div className="dlf-bm-faq__inner">
                <header>
                    <h2 id="tender-faq-heading">Veelgestelde vragen over Laravel</h2>
                </header>
                <div className="dlf-bm-faq__list">
                    {faqs.map(([question, answer], index) => {
                        const expanded = open === index;
                        const id = `tender-faq-${index}`;
                        return (
                            <div className="dlf-bm-faq__item" key={question}>
                                <Button
                                    id={`${id}-trigger`}
                                    onClick={() => setOpen(expanded ? -1 : index)}
                                    aria-expanded={expanded}
                                    aria-controls={`${id}-panel`}
                                    className="dlf-bm-faq__trigger"
                                >
                                    <span>{question}</span>
                                    <span className="dlf-bm-faq__toggle" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d={expanded ? "M5 12h14" : "M12 5v14M5 12h14"} />
                                        </svg>
                                    </span>
                                </Button>
                                <div
                                    id={`${id}-panel`}
                                    className={`dlf-bm-faq__panel${expanded ? " dlf-bm-faq__panel--open" : ""}`}
                                    role="region"
                                    aria-labelledby={`${id}-trigger`}
                                    aria-hidden={!expanded}
                                >
                                    <div>
                                        <div className="dlf-bm-faq__answer">
                                            {answer.split("\n\n").map((paragraph) => (
                                                <p key={paragraph}>{paragraph}</p>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

export default function TenderLanding({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <main className="dlf-bm dlf-rails dlf-bm--tender" data-dlf-footer-cta-stage>
                <section className="dlf-bm__head dlf-divider-section">
                    <p className="dlf-kicker">Laravel in aanbestedingen</p>
                    <h1>{page.title}</h1>
                    <p>
                        Hier vind je duidelijke informatie over waarom Laravel een veilige en
                        betrouwbare keuze is voor overheden, semi-overheidsinstellingen en grote
                        organisaties.
                    </p>
                </section>
                <section
                    className="dlf-bm-pains dlf-bm-logo-cloud dlf-divider-section"
                    aria-labelledby="tender-proof-heading"
                >
                    <header>
                        <h2 id="tender-proof-heading">
                            150 miljoen softwareprojecten wereldwijd zijn ontwikkeld in Laravel
                        </h2>
                    </header>
                    <div className="dlf-bm-grid-3 dlf-bm-logo-cloud__grid dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-2">
                        {[
                            ["diabetes-nl.svg", "diabetes.nl"],
                            ["abnamro.svg", "ABN AMRO"],
                            ["rijksoverheid.svg", "Rijksoverheid"],
                            ["avia.svg", "AVIA"],
                            ["de-hypotheekshop.svg", "De Hypotheekshop"],
                            ["nva.svg", "NVA — Nederlandse Vereniging voor Autisme"],
                        ].map(([file, alt]) => (
                            <article key={file}>
                                <img src={`/assets/img/customers/${file}`} alt={alt} />
                            </article>
                        ))}
                    </div>
                </section>
                <section
                    className="dlf-bm-objections dlf-divider-section dlf-divider-theme-inverse"
                    aria-labelledby="tender-requirements-heading"
                >
                    <div className="dlf-bm-objections__inner">
                        <header>
                            <h2 id="tender-requirements-heading">
                                Laravel voldoet aan de eisen van een aanbesteding
                            </h2>
                            <p>
                                Vier redenen waarom Laravel een veilige en toekomstbestendige keuze
                                is voor publieke en grote organisaties.
                            </p>
                        </header>
                        <div className="dlf-bm-objections__cards">
                            {requirements.map(([title, copy]) => (
                                <article key={title}>
                                    <span aria-hidden="true">“</span>
                                    <h3>{title}</h3>
                                    <p>{copy}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
                <section
                    className="dlf-bm-narrative dlf-divider-section"
                    aria-labelledby="tender-bake-heading"
                >
                    <div className="dlf-bm-narrative__inner">
                        <p className="dlf-kicker">Buy and keep evolving</p>
                        <h2 id="tender-bake-heading">
                            Laravel is een combinatie van standaard componenten én maatwerk
                        </h2>
                        <p>
                            De traditionele keuze is: koop je een standaardpakket (Buy) of laat je
                            alles op maat maken (Make)? Laravel biedt een derde optie:{" "}
                            <strong>BAKE (Buy And Keep Evolving)</strong>. Een systeem gebouwd in
                            Laravel wordt opgezet met bouwblokken en modules die al bij tientallen
                            organisaties draaien. Heb je daarnaast andere of aangepaste
                            functionaliteiten, bouwblokken of modules nodig voor jouw unieke
                            situatie? Dan wordt dit op maat ontwikkeld.
                        </p>
                    </div>
                </section>
                <section
                    className="dlf-bm-ink-band dlf-divider-section dlf-divider-theme-inverse"
                    aria-label="Voordelen van standaard componenten met maatwerk"
                >
                    <div className="dlf-bm-ink-band__inner">
                        <p className="dlf-bm-ink-band__lead">
                            Zo heb je het beste van beide werelden: de <strong>snelheid</strong> en{" "}
                            <strong>betrouwbaarheid</strong> van een standaardoplossing met de{" "}
                            <strong>flexibiliteit</strong> van maatwerk.
                        </p>
                        <div className="dlf-bm-grid-3 dlf-bm-ink-band__grid dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                            {features.map(([icon, title, copy]) => (
                                <article key={title}>
                                    <div>
                                        <img src={icon} width="44" height="44" alt="" />
                                    </div>
                                    <h3>{title}</h3>
                                    <p>{copy}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
                <section className="dlf-bm-cases dlf-bm-audience dlf-divider-section">
                    <div className="dlf-bm-cases__inner">
                        <header>
                            <h2>Praktische vervolgstappen</h2>
                        </header>
                        <div className="dlf-bm-cases__list">
                            {audiences.map(([title, copy, items, image]) => (
                                <article key={title}>
                                    <div className="dlf-bm-cases__copy">
                                        <h3>{title}</h3>
                                        <p>{copy}</p>
                                        {title === "Voor tender inschrijvers" ? (
                                            <p>
                                                Daarnaast vinden opdrachtgevers het prettig om te
                                                weten wat al (door jullie) is ontwikkeld in Laravel,
                                                dat lijkt op wat zij ook nodig hebben. Dit geeft
                                                vertrouwen dat Laravel een goede keuze is voor hun
                                                eigen systeem.
                                            </p>
                                        ) : null}
                                        {items.length ? (
                                            <ul>
                                                {items.map((item) => (
                                                    <li key={item}>{item}</li>
                                                ))}
                                            </ul>
                                        ) : null}
                                    </div>
                                    <figure data-progressive-media-frame>
                                        <img src={image} alt="" />
                                    </figure>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
                <Faq />
                <FoundationClose tender />
            </main>
        </PublicPageFrame>
    );
}
