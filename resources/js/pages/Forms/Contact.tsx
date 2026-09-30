import { ContactCard } from "@/components/forms-react/ContactCard";
import { footerCta } from "@/components/forms-react/FooterCtaAdapter";
import { FormFields } from "@/components/forms-react/FormFields";
import { FormSuccess } from "@/components/forms-react/FormSuccess";
import { type AcquisitionPageProps } from "@/components/forms-react/types";
import { SiteLayout } from "@/components/site";

export default function Contact({ acquisition, app, site }: AcquisitionPageProps) {
    const { page, form, submission } = acquisition;

    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={footerCta(page.callToAction)}>
            <div className="dlf-public-page dlf-contact-page editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <header className="dlf-public-page__hero dlf-divider-section">
                        <span className="editorial-eyebrow">Contact</span>
                        <h1>Neem contact op</h1>
                        <p>
                            Een vraag over Laravel, onze stichting of de Nederlandse community? We
                            denken graag met je mee.
                        </p>
                    </header>

                    <div className="dlf-contact-grid dlf-divider-section dlf-divider-split">
                        <div className="dlf-contact-copy">
                            <article className="editorial-article__prose dlf-public-page__prose">
                                {page.content.map((block, index) =>
                                    block.html ? (
                                        <div
                                            key={block.id ?? `${block.kind}-${index}`}
                                            className="set"
                                            data-cms-html
                                            dangerouslySetInnerHTML={{ __html: block.html }}
                                        />
                                    ) : null,
                                )}
                            </article>

                            <ContactCard organization={site.organization} />
                        </div>

                        <div className="dlf-member-form dlf-contact-form">
                            {submission.success ? (
                                <FormSuccess
                                    className="dlf-contact-form__success"
                                    heading="Bedankt voor je bericht"
                                    message="We nemen zo snel mogelijk contact met je op."
                                />
                            ) : form ? (
                                <FormFields
                                    captchaSiteKey={app.captchaSiteKey}
                                    csrfToken={app.csrfToken}
                                    form={form}
                                    redirect="/contact"
                                    submission={submission}
                                    variant="public"
                                />
                            ) : null}
                        </div>
                    </div>
                </div>
            </div>
        </SiteLayout>
    );
}
