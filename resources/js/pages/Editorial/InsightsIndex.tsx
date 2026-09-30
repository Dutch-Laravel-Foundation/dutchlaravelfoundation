import { SiteLayout } from "@/components/site";
import { ArticleIndex } from "@/components/editorial-react/ArticleIndex";

type InsightsIndexProps = {
    category?: string | null;
    editorial: App.Data.Editorial.ArticleIndexData;
    page: App.Data.Pages.HomePageData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function InsightsIndex({ category, editorial, page, site }: InsightsIndexProps) {
    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={page.footerCta}>
            <ArticleIndex
                activeCategory={category}
                baseUrl="/nieuws"
                data={editorial}
                empty={{
                    description: "Er zijn nog geen nieuwsberichten in deze categorie.",
                    linkLabel: "Bekijk al het nieuws",
                    title: "Geen berichten gevonden",
                }}
                filters={["Leden", "Netwerk", "Inspiratie", "Bestuur"]}
                heading={{
                    eyebrow: "Nieuws",
                    introduction:
                        "Events van de stichting, verhalen van onze leden en kennis uit het Nederlandse Laravel-ecosysteem.",
                    title: "Nieuws uit de community",
                }}
                paginationLabel="nieuwsberichten"
            />
        </SiteLayout>
    );
}
