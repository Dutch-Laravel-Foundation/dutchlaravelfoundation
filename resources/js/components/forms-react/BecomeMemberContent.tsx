import { SmartLink } from "@/components/ui/SmartLink";

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

const certificationBenefits = [
    <>
        <strong>€ 250,- korting</strong> op de lidmaatschapsfee van de DLF (eenmalig).
    </>,
    <>
        <strong>25% korting</strong> op het Developer Certification examen — van € 249,- voor €
        186,75 per examen.
    </>,
    <>
        <strong>25% korting</strong> op een jaar Company Certification — van € 799,- voor € 599,-,
        inclusief één Developer Certification examen.
    </>,
    <>
        Daarna betaal je als gecertificeerd bedrijf nog maar <strong>€ 99,-</strong> per Developer
        Certification examen, in plaats van € 249,-.
    </>,
];

export function BecomeMemberContent() {
    return (
        <div className="dlf-lid-left dlf-divider-list">
            <div className="dlf-lid-content-block">
                <span className="dlf-community-kicker">Voordelen voor leden</span>
                <h2 className="dlf-community-heading dlf-community-heading--small">
                    Als lid kun je rekenen op de volgende voordelen
                </h2>
                <p className="dlf-community-intro">
                    Van kennisdeling en exclusieve events tot een keurmerk, leads en scherpe
                    groepskortingen op tooling.
                </p>
                <div
                    className="dlf-lid-benefits dlf-lid-benefits--overview dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1"
                    aria-label="Voordelen voor leden"
                >
                    {benefits.map(([icon, title, copy]) => (
                        <article className="dlf-lid-benefit" key={title}>
                            <span className="dlf-community-icon">
                                <img src={icon} width="44" height="44" alt="" loading="lazy" />
                            </span>
                            <strong className="dlf-lid-benefit__title">{title}</strong>
                            <span className="dlf-lid-benefit__copy">
                                {title === "Leads & projecten" ? (
                                    <>
                                        Word gematcht met opdrachtgevers via{" "}
                                        <SmartLink href="/aanvraag">Match je project</SmartLink>.
                                    </>
                                ) : (
                                    copy
                                )}
                            </span>
                        </article>
                    ))}
                </div>
            </div>

            <div className="dlf-lid-content-block dlf-lid-events-block">
                <span className="dlf-community-kicker">Events</span>
                <h2 className="dlf-community-heading dlf-community-heading--small">
                    Toegang tot leuke DLF events
                </h2>
                <ul className="dlf-lid-list">
                    <li>
                        <span>
                            <strong>CxO diner.</strong> Een interactieve avond met andere agency
                            leaders. Onder het genot van heerlijk eten en drinken bespreken we alles
                            over het runnen van een eigen onderneming.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>LaraFest.</strong> Een zomers evenement met techtalks, BBQ,
                            drankjes en de uitreiking van de LaraAward voor het meest innovatieve
                            project.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>Meetups</strong> door het hele land.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>De jaarlijkse Laravel hackathon.</strong>
                        </span>
                    </li>
                </ul>
                <figure className="dlf-lid-events-photo" data-progressive-media-frame>
                    <img
                        src="/assets/uploads/assets/Bitmap@2x.jpg"
                        alt="Bezoekers tijdens een Dutch Laravel Foundation event"
                        width="1336"
                        height="830"
                        loading="lazy"
                    />
                </figure>
            </div>

            <div className="dlf-lid-content-block">
                <span className="dlf-community-kicker">Kortingen op tooling</span>
                <h2 className="dlf-community-heading dlf-community-heading--small">
                    Kortingen op tooling
                </h2>
                <ul className="dlf-lid-list" style={{ marginBottom: 20 }}>
                    <li>
                        <span>
                            <strong>20% korting</strong> op nieuwe JetBrains’ PhpStorm-licenties.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>Proefperiode van 3 maanden</strong> bij Sentry.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>30% korting</strong> op een abonnement bij Lettermint.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>30% korting</strong> op een abonnement bij Oh Dear.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>20% korting</strong> op een Pro of Max licentie bij NativePHP.
                        </span>
                    </li>
                    <li>
                        <span>
                            <strong>20% korting</strong> op je eerste project met Laravel Shift.
                        </span>
                    </li>
                </ul>
                <p className="dlf-community-feature-copy">
                    Aanvullende kortingen zijn momenteel in ontwikkeling. Zo hebben we lopende
                    gesprekken met Laracasts en andere partners.
                </p>
            </div>

            <div className="dlf-lid-content-block">
                <span className="dlf-community-kicker">Laravel Company Certified</span>
                <h2 className="dlf-community-heading dlf-community-heading--small">
                    Extra voordeel als je Laravel Company Certified bent
                </h2>
                <div className="dlf-lid-certified-list">
                    {certificationBenefits.map((benefit, index) => (
                        <div key={index}>
                            <span className="dlf-lid-checkmark" aria-hidden="true">
                                ✓
                            </span>
                            <span>{benefit}</span>
                        </div>
                    ))}
                </div>
            </div>

            <div className="dlf-lid-reason dlf-divider-tail-segment">
                <div className="dlf-lid-reason__copy">
                    <span className="dlf-community-kicker">De echte reden</span>
                    <h2 className="dlf-community-heading dlf-community-heading--small">
                        Uiteraard zijn de voordelen fijn, maar die zijn niet het belangrijkste
                    </h2>
                    <p className="dlf-community-copy">
                        De echte reden om lid te worden, naar onze mening, is onze community. Stuk
                        voor stuk Laravel fanaten die het leuk vinden om kennis te delen en te
                        sparren. Het is een ideale manier om je netwerk te vergroten, en een mooie
                        manier om wat terug te doen voor Laravel in Nederland.
                    </p>
                    <p className="dlf-community-copy">
                        Alle inkomsten vloeien direct in initiatieven die aansluiten bij de
                        bovenstaande gedachte.
                    </p>
                </div>
            </div>
        </div>
    );
}
