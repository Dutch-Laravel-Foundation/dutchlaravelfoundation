import { SiteLayout } from "@/components/site";
import { ArticleBody } from "@/components/editorial-react/ArticleBody";
import { ArticleHero } from "@/components/editorial-react/ArticleHero";
import { KnowledgeAuthors } from "@/components/editorial-react/Authors";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { footerCta } from "@/components/editorial-react/FooterCtaAdapter";

type KnowledgeShowProps = {
    editorial: App.Data.Editorial.KnowledgeData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function KnowledgeShow({ editorial, site }: KnowledgeShowProps) {
    return (
        <SiteLayout
            data={site}
            pageSlug={editorial.slug}
            footerCta={footerCta(editorial.callToAction)}
        >

            <div className="editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <Breadcrumb
                        href="/kennis"
                        label="Kennis"
                        current={editorial.category ?? "Kennis"}
                    />
                    <ArticleHero
                        authors={editorial.authors}
                        category={editorial.category}
                        date={editorial.date}
                        featuredImage={editorial.featuredImage}
                        introduction={editorial.introduction}
                        title={editorial.title}
                    />
                    <ArticleBody label="In dit artikel" html={editorial.contentHtml} />
                    <KnowledgeAuthors authors={editorial.authors} />
                </div>
            </div>
        </SiteLayout>
    );
}
