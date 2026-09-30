import { SiteLayout } from "@/components/site";
import { ArticleIndex } from "@/components/editorial-react/ArticleIndex";

type KnowledgeIndexProps = {
    category?: string | null;
    editorial: App.Data.Editorial.ArticleIndexData;
    page: App.Data.Pages.HomePageData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function KnowledgeIndex({ category, editorial, page, site }: KnowledgeIndexProps) {
    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={page.footerCta}>
            <ArticleIndex
                activeCategory={category}
                baseUrl="/kennis"
                data={editorial}
                empty={{
                    description: "Er zijn nog geen kennisartikelen in deze categorie.",
                    linkLabel: "Bekijk alle kennis",
                    title: "Geen artikelen gevonden",
                }}
                filters={["Leden", "Netwerk", "Inspiratie", "Tooling"]}
                heading={{
                    eyebrow: "Kennis",
                    introduction:
                        "Verdieping, praktijkkennis en best practices uit het Nederlandse Laravel-ecosysteem over toegankelijkheid, security, performance en tooling.",
                    title: "Kennis uit de community",
                }}
                paginationLabel="kennisartikelen"
            />
        </SiteLayout>
    );
}
