import { SiteLayout } from "@/components/site";
import { ArticleBody } from "@/components/editorial-react/ArticleBody";
import { ArticleHero } from "@/components/editorial-react/ArticleHero";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { ContentBlocks } from "@/components/editorial-react/ContentBlocks";
import { EventFacts } from "@/components/editorial-react/EventFacts";

type EventsShowProps = {
    editorial: App.Data.Editorial.EventData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function EventsShow({ editorial, site }: EventsShowProps) {
    return (
        <SiteLayout data={site} pageSlug={editorial.slug} footerCta={null}>

            <div className="editorial-article editorial-event">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <Breadcrumb
                        href="/agenda"
                        label="Agenda"
                        current={editorial.type ?? "Evenement"}
                    />
                    <ArticleHero
                        category={editorial.type}
                        date={editorial.dateStart}
                        featuredImage={editorial.featuredImage}
                        introduction={editorial.introduction}
                        title={editorial.title}
                    />
                    <ArticleBody label="Op deze pagina">
                        <ContentBlocks blocks={editorial.content} />
                        <EventFacts event={editorial} />
                    </ArticleBody>
                </div>
            </div>
        </SiteLayout>
    );
}
