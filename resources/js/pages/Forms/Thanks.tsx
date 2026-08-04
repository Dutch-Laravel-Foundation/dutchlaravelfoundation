import { footerCta } from "@/components/forms-react/FooterCtaAdapter";
import { type AcquisitionPageProps } from "@/components/forms-react/types";
import { SiteLayout } from "@/components/site";

export default function Thanks({ acquisition, site }: AcquisitionPageProps) {
    const { page } = acquisition;

    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={footerCta(page.callToAction)}>
            <section className="dlf-aanvraag-thanks">
                <div className="dlf-aanvraag-thanks__rail" data-dlf-footer-cta-stage>
                    <span className="dlf-aanvraag-thanks__side" aria-hidden="true" />
                    <div className="dlf-form-success dlf-aanvraag-thanks__content">
                        <span className="dlf-form-success__icon" aria-hidden="true">
                            <img
                                src="/assets/img/development-speed.svg"
                                width="44"
                                height="44"
                                alt=""
                            />
                        </span>
                        <h1>Bedankt voor je aanvraag!</h1>
                        <p>
                            Wij hebben je aanvraag ontvangen en nemen zo spoedig mogelijk contact
                            met je op. We streven ernaar om binnen 2 werkdagen te reageren.
                        </p>
                    </div>
                    <span className="dlf-aanvraag-thanks__side" aria-hidden="true" />
                </div>
            </section>
        </SiteLayout>
    );
}
