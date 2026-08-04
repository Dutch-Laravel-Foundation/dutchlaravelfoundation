import { BecomeMemberContent } from "@/components/forms-react/BecomeMemberContent";
import { footerCta } from "@/components/forms-react/FooterCtaAdapter";
import { FormFields } from "@/components/forms-react/FormFields";
import { FormSuccess } from "@/components/forms-react/FormSuccess";
import { type AcquisitionPageProps } from "@/components/forms-react/types";
import { SiteLayout } from "@/components/site";

export default function BecomeMember({ acquisition, app, site }: AcquisitionPageProps) {
    const { page, form, submission } = acquisition;

    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={footerCta(page.callToAction)}>
            <div className="dlf-community-page dlf-become-member-page" data-dlf-footer-cta-stage>
                <section
                    className="dlf-community-section dlf-lid-grid dlf-divider-section dlf-divider-split"
                    aria-labelledby="become-member-heading"
                >
                    <div className="dlf-lid-hero-copy">
                        <span className="dlf-community-kicker">{page.title}</span>
                        <h1 id="become-member-heading" className="dlf-community-title">
                            Word lid van de Dutch Laravel Foundation en geniet van veel voordelen
                        </h1>
                        <p className="dlf-community-intro">
                            Zowel bureaus, ZZP’ers als ontwikkelteams die met Laravel werken kunnen
                            lid worden van de Dutch Laravel Foundation. Naast toegang tot een groot
                            netwerk van Laravel specialisten krijg je exclusief toegang tot Laravel
                            events en een keurmerk voor je organisatie. Bureaus en ZZP’ers krijgen
                            daarnaast toegang tot leads van potentiële opdrachtgevers.
                        </p>
                    </div>
                    <figure className="dlf-community-photo" data-progressive-media-frame>
                        <img
                            src="/assets/img/developers-achter-bureau.jpg"
                            alt="Laravel developers aan het werk"
                            width="2048"
                            height="1366"
                            loading="eager"
                            fetchPriority="high"
                        />
                    </figure>
                </section>

                <section
                    className="dlf-lid-grid dlf-divider-section dlf-divider-section--composite-tail dlf-divider-split"
                    aria-label="Voordelen en lidmaatschapsformulier"
                >
                    <BecomeMemberContent />

                    <aside
                        className="dlf-lid-right dlf-divider-tail-segment"
                        id="member-form"
                        aria-label="Aanmelden als lid"
                    >
                        <div className="dlf-lid-form">
                            <div className="dlf-lid-pricing">
                                <div className="dlf-lid-pricing__top">
                                    <span className="dlf-community-kicker">Lidmaatschap</span>
                                </div>
                                <div className="dlf-lid-price">
                                    <span>
                                        <strong className="dlf-lid-price__type">ZZP</strong>
                                        <span className="dlf-lid-price__period">per jaar</span>
                                    </span>
                                    <strong className="dlf-lid-price__amount">€ 450,-</strong>
                                </div>
                                <div className="dlf-lid-price">
                                    <span>
                                        <strong className="dlf-lid-price__type">
                                            Bureaus &amp; ontwikkelteams
                                        </strong>
                                        <span className="dlf-lid-price__period">per jaar</span>
                                    </span>
                                    <strong className="dlf-lid-price__amount">€ 1.250,-</strong>
                                </div>
                            </div>

                            <div className="dlf-member-form">
                                {submission.success ? (
                                    <FormSuccess
                                        heading="Bedankt voor je interesse!"
                                        headingClassName="dlf-community-heading dlf-community-heading--small"
                                        message="Wij nemen zo spoedig mogelijk contact met je op."
                                    />
                                ) : form ? (
                                    <FormFields
                                        captchaSiteKey={app.captchaSiteKey}
                                        csrfToken={app.csrfToken}
                                        form={form}
                                        redirect="/lid-worden"
                                        submission={submission}
                                        variant="community"
                                    />
                                ) : null}
                            </div>
                        </div>
                    </aside>
                </section>
            </div>
        </SiteLayout>
    );
}
