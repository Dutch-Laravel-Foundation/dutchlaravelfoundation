import { footerCta } from "@/components/forms-react/FooterCtaAdapter";
import { SalesFunnelWizard } from "@/components/forms-react/SalesFunnelWizard";
import { type AcquisitionPageProps } from "@/components/forms-react/types";
import { SiteLayout } from "@/components/site";

export default function SalesFunnel({ acquisition, app, site }: AcquisitionPageProps) {
    const { page, form, submission } = acquisition;

    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={footerCta(page.callToAction)}>
            <div className="bg-white">
                <h1 className="sr-only">Match je Laravel-project</h1>
                <div
                    className="container dlf-aanvraag-container mx-auto border-x border-[#ececec]"
                    data-dlf-footer-cta-stage
                >
                    <div className="w-full bg-white">
                        {form ? (
                            <SalesFunnelWizard
                                captchaSiteKey={app.captchaSiteKey}
                                csrfToken={app.csrfToken}
                                form={form}
                                submission={submission}
                            />
                        ) : null}
                    </div>
                </div>
            </div>
        </SiteLayout>
    );
}
