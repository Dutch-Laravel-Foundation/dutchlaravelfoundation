import { SiteLayout } from "@/components/site";
import { ArticleBody } from "@/components/editorial-react/ArticleBody";
import { ArticleHero } from "@/components/editorial-react/ArticleHero";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { ContentBlocks } from "@/components/editorial-react/ContentBlocks";
import { footerCta } from "@/components/editorial-react/FooterCtaAdapter";
import { InsightAuthor } from "@/components/editorial-react/Authors";

type InsightsShowProps = {
    editorial: App.Data.Editorial.InsightData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function InsightsShow({ editorial, site }: InsightsShowProps) {
    return (
        <SiteLayout
            data={site}
            pageSlug={editorial.slug}
            footerCta={editorial.callToAction ? footerCta(editorial.callToAction) : undefined}
        >

            <div className="editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <Breadcrumb
                        href="/nieuws"
                        label="Nieuws"
                        current={editorial.category ?? "Nieuws"}
                    />
                    <ArticleHero
                        author={editorial.author}
                        category={editorial.category}
                        date={editorial.date}
                        featuredImage={editorial.featuredImage}
                        introduction={editorial.introduction}
                        title={editorial.title}
                    />
                    <ArticleBody label="In dit artikel">
                        <ContentBlocks blocks={editorial.content} />
                    </ArticleBody>
                    <InsightAuthor author={editorial.author} />
                </div>
            </div>
        </SiteLayout>
    );
}
