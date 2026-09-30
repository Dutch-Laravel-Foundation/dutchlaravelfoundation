import { communityFooterCta } from "@/components/community-react/CommunityFooterCtaAdapter";
import { CommunityContentBlocks } from "@/components/community-react/CommunityContentBlocks";
import { LarabellesArticleBody } from "@/components/community-react/LarabellesArticleBody";
import { SiteLayout } from "@/components/site";
import { DlfButtonLink } from "@/components/ui/DlfButton";

type LarabellesProps = {
    community: App.Data.Community.LarabellesData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function Larabelles({ community, site }: LarabellesProps) {
    const { page } = community;

    return (
        <SiteLayout
            data={site}
            pageSlug={page.slug}
            footerCta={page.callToAction ? communityFooterCta(page.callToAction) : undefined}
        >

            <div className="dlf-public-page dlf-public-page--larabelles editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <header className="dlf-public-page__hero dlf-public-page__hero--split dlf-divider-section">
                        <div>
                            <span className="editorial-eyebrow">Community</span>
                            <h1>{page.title}</h1>
                            <p>
                                Een internationale community die vrouwelijke Laravel-developers met
                                elkaar verbindt, ondersteunt en zichtbaar maakt.
                            </p>
                        </div>
                        <img
                            src="/assets/img/larabelles.png"
                            alt="Larabelles"
                            width="320"
                            height="120"
                        />
                    </header>

                    <LarabellesArticleBody>
                        <CommunityContentBlocks blocks={page.content} />
                        <figure className="dlf-larabelles-mission" data-progressive-media-frame>
                            <img
                                src="/assets/img/larabelles-mission.png"
                                alt="Our Mission – Elevating Our Community, Connecting and Engaging, Resource Sharing"
                                width="1024"
                                height="369"
                                loading="lazy"
                                decoding="async"
                                data-progressive-media
                                data-media-state="loaded"
                            />
                        </figure>
                        <div className="dlf-public-actions">
                            <DlfButtonLink
                                href="https://www.larabelles.com/"
                                target="_blank"
                                rel="noopener noreferrer"
                                face="red"
                                shadow="red"
                            >
                                Sluit je aan bij de Larabelles
                            </DlfButtonLink>
                        </div>
                    </LarabellesArticleBody>
                </div>
            </div>
        </SiteLayout>
    );
}
