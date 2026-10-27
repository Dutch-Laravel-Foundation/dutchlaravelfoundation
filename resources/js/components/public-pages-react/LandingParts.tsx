import { DlfButtonLink } from "@/components/ui/DlfButton";

import { ProgressiveImage } from "./ProgressiveImage";

type Case = App.Data.PublicPages.LandingCaseData;

export function LaravelMark() {
    return (
        <div className="dlf-bm-objections__mark" aria-hidden="true">
            <svg viewBox="0 0 265 271" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M264.712 61.2904C264.712 61.1474 264.569 61.0045 264.569 61.0045C264.425 60.7186 264.425 60.5756 264.281 60.2897C264.137 60.1468 264.137 60.0038 263.993 59.8609C263.85 59.7179 263.706 59.575 263.562 59.432C263.418 59.2891 263.275 59.2891 263.131 59.1461C262.987 59.0032 262.843 58.8602 262.699 58.7172L211.511 29.4122C210.217 28.6975 208.635 28.6975 207.198 29.4122L155.865 58.7172C155.722 58.8602 155.578 59.0032 155.434 59.1461C155.29 59.2891 155.146 59.2891 155.003 59.432C154.859 59.575 154.715 59.7179 154.571 59.8609C154.428 60.0038 154.428 60.1468 154.284 60.2897C154.14 60.4327 154.14 60.7186 153.996 61.0045C153.996 61.1474 153.852 61.2904 153.852 61.2904C153.709 61.5763 153.709 62.0051 153.709 62.434V118.185L111.004 142.63V33.5578C111.004 33.129 111.004 32.8431 110.86 32.4142C110.86 32.2713 110.716 32.1283 110.716 32.1283C110.572 31.8424 110.572 31.6995 110.429 31.4136C110.285 31.2706 110.285 31.1276 110.141 30.9847C109.997 30.8417 109.853 30.6988 109.71 30.5559C109.566 30.4129 109.422 30.4129 109.278 30.2699C109.135 30.127 108.991 29.984 108.847 29.8411L57.6587 0.536068C56.3646 -0.178689 54.783 -0.178689 53.3451 0.536068L2.15681 29.8411C2.01302 29.984 1.86923 30.127 1.72544 30.2699C1.58166 30.4129 1.43786 30.4129 1.29408 30.5559C1.15029 30.6988 1.0065 30.8417 0.862711 30.9847C0.718923 31.1276 0.718928 31.2706 0.57514 31.4136C0.431353 31.5565 0.431358 31.8424 0.28757 32.1283C0.28757 32.2713 0.143774 32.4142 0.143774 32.4142C-1.31663e-05 32.7001 0 33.129 0 33.5578V208.101C0 209.674 0.862724 210.96 2.15681 211.818L104.677 270.428C104.965 270.571 105.109 270.571 105.396 270.714C105.54 270.714 105.684 270.857 105.684 270.857C105.971 271 106.403 271 106.834 271C107.265 271 107.553 271 107.984 270.857C108.128 270.857 108.128 270.714 108.272 270.714C108.559 270.571 108.847 270.571 108.991 270.428L211.511 211.818C212.805 211.103 213.668 209.674 213.668 208.101V152.35L262.843 124.189C264.137 123.474 265 122.045 265 120.472V62.291C264.856 62.0051 264.856 61.5763 264.712 61.2904ZM106.834 203.241L64.2729 179.225L108.991 153.637L158.166 125.476L200.871 149.92L169.381 167.646L106.834 203.241ZM205.041 94.1692V142.487L187.067 132.194L162.336 118.042V69.7245L180.309 80.017L205.041 94.1692ZM209.354 37.9893L252.059 62.434L209.354 86.8787L166.649 62.434L209.354 37.9893ZM77.7889 161.642L59.8155 171.935V65.436L84.5469 51.2838L102.52 40.9913V147.49L77.7889 161.642ZM55.5019 9.11315L98.2067 33.5578L55.5019 58.0025L12.7971 33.5578L55.5019 9.11315ZM8.62724 40.8483L26.6007 51.1408L51.3321 65.293V179.225C51.3321 179.368 51.3321 179.511 51.3321 179.654C51.3321 179.797 51.3321 180.083 51.4758 180.226C51.4758 180.369 51.6196 180.512 51.6196 180.655C51.7634 180.798 51.7634 181.084 51.9072 181.227C52.051 181.37 52.051 181.512 52.1948 181.655C52.3386 181.798 52.3386 181.941 52.4824 182.084C52.6262 182.227 52.7699 182.227 52.9137 182.37C53.0575 182.513 53.2013 182.656 53.3451 182.656L102.377 210.246V259.135L8.33965 205.385V40.8483H8.62724ZM205.041 205.671L111.004 259.421V210.532L180.597 171.077L205.041 157.211V205.671ZM256.229 118.042L213.524 142.487V94.1692L238.256 80.017L256.229 69.7245V118.042Z" />
            </svg>
        </div>
    );
}

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
