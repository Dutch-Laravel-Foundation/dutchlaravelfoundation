import { SiteLayout } from "@/components/site";
import { ClientLogoWall } from "@/components/home/ClientLogoWall";
import { CurrentCommunity } from "@/components/home/CurrentCommunity";
import { HighlightedInsight } from "@/components/home/HighlightedInsight";
import { HomeHero } from "@/components/home/HomeHero";
import { PartnerMarquee } from "@/components/home/PartnerMarquee";

type HomeProps = {
    page: App.Data.Pages.HomePageData;
    home: App.Data.Home.HomeData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function Home({ page, home, site }: HomeProps) {
    return (
        <SiteLayout data={site} pageSlug={page.slug}>
            <HomeHero page={page} />

            <main className="dlf-home-main dlf-rails" data-dlf-footer-cta-stage>
                <CurrentCommunity
                    latestInsight={home.latestInsight}
                    latestKnowledge={home.latestKnowledge}
                />
                <PartnerMarquee partners={home.partners} />
                {home.highlightedInsight ? (
                    <HighlightedInsight insight={home.highlightedInsight} />
                ) : null}
                <ClientLogoWall clients={home.clients} />
            </main>
        </SiteLayout>
    );
}
