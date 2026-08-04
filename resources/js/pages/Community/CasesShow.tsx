import { CommunityContentBlocks } from "@/components/community-react/CommunityContentBlocks";
import { CommunityImage } from "@/components/community-react/CommunityImage";
import { ArticleBody } from "@/components/editorial-react/ArticleBody";
import { ArticleHero } from "@/components/editorial-react/ArticleHero";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { SiteLayout } from "@/components/site";
import { DlfButtonLink } from "@/components/ui/DlfButton";

type CasesShowProps = {
    community: App.Data.Community.CaseData;
    site: App.Data.SiteShell.SiteShellData;
};

function CaseAttribution({ data }: { data: App.Data.Community.CaseData }) {
    if (!data.member?.title && !data.client?.title) {
        return null;
    }

    const memberHref = data.member?.url ?? data.member?.uri;

    return (
        <section
            className="editorial-author editorial-case-attribution dlf-divider-section"
            aria-labelledby="case-attribution-title"
        >
            <span className="editorial-author__label">Projectinformatie</span>
            <div className="editorial-author__grid">
                <div className="editorial-author__content">
                    {data.member?.logo ? (
                        <div className="editorial-case-attribution__logo">
                            <CommunityImage
                                asset={data.member.logo}
                                title={`Logo van ${data.member.title}`}
                            />
                        </div>
                    ) : null}
                    <div className="editorial-author__details">
                        <h2 id="case-attribution-title">Over deze case</h2>
                        <div className="editorial-author__bio">
                            {data.member?.title ? (
                                <p>
                                    <strong>Gerealiseerd door:</strong> {data.member.title}
                                </p>
                            ) : null}
                            {data.client?.title ? (
                                <p>
                                    <strong>Opdrachtgever:</strong> {data.client.title}
                                </p>
                            ) : null}
                        </div>
                        {memberHref && data.member ? (
                            <DlfButtonLink
                                className="editorial-author__link"
                                href={memberHref}
                                face="red"
                                shadow="red"
                            >
                                Bekijk {data.member.title}
                            </DlfButtonLink>
                        ) : null}
                    </div>
                </div>
            </div>
        </section>
    );
}

export default function CasesShow({ community, site }: CasesShowProps) {
    return (
        <SiteLayout data={site} pageSlug={community.slug}>

            <div className="editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <Breadcrumb
                        href="/cases"
                        label="Cases"
                        current={community.member?.title ?? "Case"}
                    />
                    <ArticleHero
                        category={community.member?.title ?? "Case"}
                        featuredImage={community.featuredImage}
                        introduction={community.introductionHtml}
                        title={community.displayTitle}
                    />
                    <ArticleBody label="In deze case">
                        <CommunityContentBlocks blocks={community.content} />
                    </ArticleBody>
                    <CaseAttribution data={community} />
                </div>
            </div>
        </SiteLayout>
    );
}
