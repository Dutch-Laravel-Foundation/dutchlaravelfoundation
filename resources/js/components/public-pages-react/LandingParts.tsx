import { DlfButtonLink } from "@/components/ui/DlfButton";

import { ProgressiveImage } from "./ProgressiveImage";

type Case = App.Data.PublicPages.LandingCaseData;

export function PhotoBand({ framework = false }: { framework?: boolean }) {
    return (
        <section className="dlf-bm-photo-band dlf-divider-section">
            <picture
                className="dlf-bm-photo-band__backdrop"
                aria-hidden="true"
                data-progressive-media-frame
            >
                <source
                    srcSet="/assets/redesign/photos/kantoor-developers-1280.webp 1280w, /assets/redesign/photos/kantoor-developers-2048.webp 2048w"
                    sizes="100vw"
                    type="image/webp"
                />
                <ProgressiveImage
                    src="/assets/redesign/photos/kantoor-developers-1280.webp"
                    alt=""
                    width="1280"
                    height="854"
                    loading="lazy"
                    decoding="async"
                />
            </picture>
            <div className="dlf-bm-photo-band__inner">
                <div className="dlf-bm-photo-band__statement">
                    <p>
                        {framework ? (
                            "Waarom Laravel-applicaties worden gekozen door IT-managers en CTO’s"
                        ) : (
                            <>
                                Deze problemen lossen zich niet vanzelf op, maar een systeem op maat
                                wél. <span>En da&apos;s betaalbaarder dan je denkt!</span>
                            </>
                        )}
                    </p>
                </div>
                <div className="dlf-bm-photo-band__benefits">
                    <dl>
                        {(framework
                            ? [
                                  ["MVP binnen no time", "Dankzij kant-en-klare componenten"],
                                  ["Eenvoudig onderhoud", "Uniforme codebase"],
                                  ["Geen vendor lock-in", "Duizenden developers beschikbaar in NL"],
                              ]
                            : [
                                  [
                                      "Precies wat jij nodig hebt",
                                      "Maatwerk sluit perfect aan bij je wensen",
                                  ],
                                  [
                                      "Snel van idee naar systeem",
                                      "Door slimme bouwblokken gaat het sneller",
                                  ],
                                  [
                                      "Groot aanbod ontwikkelpartners",
                                      "Nooit meer afhankelijk van één partij",
                                  ],
                              ]
                        ).map(([term, description]) => (
                            <div key={term}>
                                <dt>{term}</dt>
                                <dd>{description}</dd>
                            </div>
                        ))}
                    </dl>
                    <DlfButtonLink href="/aanvraag" face="black" shadow="black">
                        Vind een ervaren ontwikkelaar
                    </DlfButtonLink>
                </div>
            </div>
        </section>
    );
}

export function LandingCases({
    cases,
    heading,
    introduction,
}: {
    cases: Case[];
    heading: string;
    introduction: string;
}) {
    return (
        <section className="dlf-bm-cases dlf-divider-section">
            <div className="dlf-bm-cases__inner">
                <header>
                    <h2>{heading}</h2>
                    <p>{introduction}</p>
                </header>
                <div className="dlf-bm-cases__list">
                    {cases.map((item) => (
                        <article key={item.id}>
                            <div className="dlf-bm-cases__copy">
                                <p className="dlf-kicker">{item.title}</p>
                                <h3>{item.longTitle ?? item.title}</h3>
                                {item.introductionHtml ? (
                                    <div
                                        data-cms-html
                                        dangerouslySetInnerHTML={{ __html: item.introductionHtml }}
                                    />
                                ) : null}
                                <DlfButtonLink
                                    href={item.url ?? `/cases/${item.slug}`}
                                    face="red"
                                    shadow="red"
                                >
                                    Lees meer
                                </DlfButtonLink>
                            </div>
                            {item.featuredImage ? (
                                <figure data-progressive-media-frame>
                                    <ProgressiveImage
                                        src={item.featuredImage.url ?? undefined}
                                        alt={item.featuredImage.alt ?? item.title}
                                        style={
                                            item.featuredImage.focusCss
                                                ? { objectPosition: item.featuredImage.focusCss }
                                                : undefined
                                        }
                                        width={item.featuredImage.width ?? undefined}
                                        height={item.featuredImage.height ?? undefined}
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </figure>
                            ) : null}
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}

export function ContactSection({ framework = false }: { framework?: boolean }) {
    return (
        <section className="dlf-bm-efficient dlf-divider-section">
            <div className="dlf-bm-split dlf-divider-split dlf-divider-split--stacked-reversed">
                <div>
                    <h2>
                        {framework
                            ? "Kom in contact met Laravel-specialisten"
                            : "Wil je stoppen met gehannes en eindelijk efficiënt werken?"}
                    </h2>
                    <p>
                        {framework
                            ? "Bekijk aangesloten Laravel-specialisten en begin een gesprek over jouw toekomstige maatwerk systeem."
                            : "Met een systeem op maat, gemaakt in Laravel, is dat mogelijk. Stap van Excel, handmatig werk of een standaardsysteem over op iets dat écht naadloos past bij je processen. Met Laravel zie je vaak al binnen weken na projectstart de eerste resultaten."}
                    </p>
                </div>
                <figure data-progressive-media-frame>
                    <ProgressiveImage
                        src={
                            framework
                                ? "/assets/img/developer-achter-bureau.jpg"
                                : "/assets/redesign/photos/developer-bureau-1200.webp"
                        }
                        alt="Developer aan het werk"
                        width="1200"
                        height="800"
                        loading="lazy"
                        decoding="async"
                    />
                </figure>
            </div>
            <div className="dlf-bm-grid-2 dlf-bm-efficient__actions dlf-divider-region dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                <article>
                    <div className="dlf-bm-efficient__icon" aria-hidden="true">
                        <img src="/assets/img/magnifier.svg?v=red2" width="22" height="22" alt="" />
                    </div>
                    <h3>Vind een gecertificeerde Laravel-ontwikkelaar bij jou in de buurt</h3>
                    <DlfButtonLink href="/aanvraag" face="red" shadow="red">
                        Plan {framework ? "een " : ""}vrijblijvende kennismaking
                    </DlfButtonLink>
                </article>
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
                        Heb je nog vragen over Laravel en of het geschikt is voor jou? Wij
                        antwoorden je zo snel mogelijk.
                    </h3>
                    <DlfButtonLink
                        href={framework ? "mailto:info@dutchlaravelfoundation.nl" : "/contact"}
                        face="outline-red"
                        shadow="red"
                    >
                        Mail {framework ? "de " : ""}Dutch Laravel Foundation
                    </DlfButtonLink>
                </article>
            </div>
        </section>
    );
}

export function FoundationClose({ tender = false }: { tender?: boolean }) {
    const values = tender
        ? [
              [
                  "/assets/img/connecting.svg",
                  "Wij verbinden",
                  "Wij brengen bedrijven in contact met de beste Laravel-ontwikkelaars in Nederland.",
              ],
              [
                  "/assets/img/assuring-quality.svg",
                  "Wij waarborgen de kwaliteit",
                  "Wij stimuleren dat onze leden voldoen aan professionele standaarden.",
              ],
              [
                  "/assets/img/boosting-knowledge.svg",
                  "Wij boosten technische kennis",
                  "Wij organiseren talks en events voor kennisuitwisseling tussen ontwikkelaars.",
              ],
          ]
        : [
              [
                  "/assets/img/connecting.svg",
                  "Wij verbinden",
                  "Wij brengen bedrijven in contact met de beste Laravel-ontwikkelaars in Nederland.",
              ],
              [
                  "/assets/img/assuring-quality.svg",
                  "Wereldwijde community",
                  "Wij stimuleren dat onze leden voldoen aan professionele standaarden.",
              ],
              [
                  "/assets/img/boosting-knowledge.svg",
                  "100% toekomstbestendig",
                  "Wij organiseren talks en events voor kennisuitwisseling tussen ontwikkelaars.",
              ],
          ];
    return (
        <>
            <section className="dlf-bm-split dlf-bm-about dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed">
                <div>
                    <h2>Wat is de Dutch Laravel Foundation?</h2>
                    <p>
                        Wij maken Laravel bekender bij Nederlandse bedrijven en bevorderen
                        Laravel-ontwikkelaars continu beter te worden door kennis te delen. Met
                        goedkeuring van Taylor Otwell{tender ? " (grondlegger van Laravel)" : ""}{" "}
                        helpen wij Nederlandse bedrijven de juiste keuzes te maken bij
                        maatwerksoftware. Wij hebben hierin geen commercieel belang maar wél de
                        expertise om je te begeleiden naar de beste oplossing.
                    </p>
                </div>
                <figure data-progressive-media-frame>
                    <ProgressiveImage
                        src={
                            tender
                                ? "/assets/img/larafest-sylvester-damgaard.jpg"
                                : "/assets/redesign/photos/dlf-larafest-talk.png"
                        }
                        alt="Laravel"
                        width="1200"
                        height="800"
                        loading="lazy"
                        decoding="async"
                    />
                </figure>
            </section>
            <div className="dlf-bm-grid-3 dlf-bm-foundation-values dlf-divider-section dlf-divider-grid dlf-divider-grid--desktop-3 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                {values.map(([icon, title, copy]) => (
                    <article key={title}>
                        <div>
                            <img src={icon} width="44" height="44" alt="" />
                        </div>
                        <h3>{title}</h3>
                        <p>{copy}</p>
                    </article>
                ))}
            </div>
        </>
    );
}
