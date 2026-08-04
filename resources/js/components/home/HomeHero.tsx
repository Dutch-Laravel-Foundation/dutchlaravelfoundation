import { DlfButtonLink } from "@/components/ui/DlfButton";

import { ProgressiveImage } from "./ProgressiveImage";

export function HomeHero({ page }: { page: App.Data.Pages.HomePageData }) {
    return (
        <section
            className="dlf-home-hero dlf-divider-section dlf-divider-split"
            aria-labelledby="home-heading"
        >
            <div className="dlf-home-hero__copy">
                <div>
                    <h1 id="home-heading">{page.headerTitle ?? page.title}</h1>
                    {page.headerContent ? (
                        <div
                            className="dlf-home-hero__intro"
                            data-cms-html
                            dangerouslySetInnerHTML={{ __html: page.headerContent }}
                        />
                    ) : null}
                    <div className="dlf-actions">
                        <DlfButtonLink href="/lid-worden" face="red" shadow="red">
                            Voor ontwikkelaars
                        </DlfButtonLink>
                        <DlfButtonLink
                            href="/een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt"
                            face="outline-red"
                            shadow="red"
                        >
                            Voor opdrachtgevers
                        </DlfButtonLink>
                    </div>
                </div>
            </div>

            <figure className="dlf-home-hero__photo" data-progressive-media-frame>
                <picture>
                    <source
                        type="image/webp"
                        srcSet="/assets/redesign/photos/larafest-groepsfoto-640.webp 640w, /assets/redesign/photos/larafest-groepsfoto-800.webp 800w, /assets/redesign/photos/larafest-groepsfoto-960.webp 960w, /assets/redesign/photos/larafest-groepsfoto-1280.webp 1280w, /assets/redesign/photos/larafest-groepsfoto-1920.webp 1920w"
                        sizes="(min-width: 1024px) 50vw, 100vw"
                    />
                    <ProgressiveImage
                        src="/assets/redesign/photos/larafest-groepsfoto.jpeg"
                        alt="Laravel developers tijdens een bijeenkomst van de Dutch Laravel Foundation"
                        width="1920"
                        height="1272"
                        loading="eager"
                        decoding="async"
                        fetchPriority="high"
                    />
                </picture>
            </figure>
        </section>
    );
}
