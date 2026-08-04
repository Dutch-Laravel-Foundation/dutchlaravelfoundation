import { ContentBlocks, PublicArticleBody, PublicPageFrame } from "@/components/public-pages-react";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };

export default function Default({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <div className="dlf-public-page editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <header className="dlf-public-page__hero dlf-divider-section">
                        <span className="editorial-eyebrow">Dutch Laravel Foundation</span>
                        <h1>{page.title}</h1>
                    </header>
                    <PublicArticleBody label="Op deze pagina">
                        <ContentBlocks blocks={page.content} />
                    </PublicArticleBody>
                </div>
            </div>
        </PublicPageFrame>
    );
}
