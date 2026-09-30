import { CommunityImage } from "@/components/community-react/CommunityImage";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { SiteLayout } from "@/components/site";
import { DlfButtonLink } from "@/components/ui/DlfButton";
import { SmartLink } from "@/components/ui/SmartLink";

type InternshipsShowProps = {
    community: App.Data.Community.InternshipData;
    site: App.Data.SiteShell.SiteShellData;
};

function externalUrl(value: string): string {
    return /^https?:\/\//i.test(value) ? value : `https://${value}`;
}

function phoneUrl(value: string): string {
    return value.replace("(0)", "").replace(/[^+0-9]/g, "");
}

export default function InternshipsShow({ community, site }: InternshipsShowProps) {
    const { member } = community;
    const memberHref = member.url ?? member.uri ?? `/leden/${member.slug}`;
    const website = member.website ? externalUrl(member.website) : null;
    const contact = member.internshipContact;
    const hasContact = Boolean(contact?.name || contact?.email || contact?.phone);
    return (
        <SiteLayout data={site} pageSlug={community.slug}>
            <div
                className="dlf-community-page dlf-internship-detail-page"
                data-dlf-footer-cta-stage
            >
                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-labelledby="internship-heading"
                >
                    <Breadcrumb href="/stagebank" label="Stagebank" current={community.title} />
                    <div
                        className={`dlf-community-head dlf-member-detail-head dlf-divider-split${member.logo ? " dlf-member-detail-head--with-logo" : ""}`}
                    >
                        <div className="dlf-member-detail-head__copy">
                            <h1 id="internship-heading" className="dlf-community-title">
                                {community.title}
                            </h1>
                            <dl className="dlf-detail-properties">
                                {member.city || member.province ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-0.5!">Locatie</dt>
                                        <dd className="m-0 text-base text-tertiary-dark">
                                            {[member.city, member.province]
                                                .filter(Boolean)
                                                .join(", ")}
                                        </dd>
                                    </div>
                                ) : null}
                                {website && member.website ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-0.5!">Website</dt>
                                        <dd className="m-0 text-base">
                                            <SmartLink
                                                className="dlf-member-detail-head__website"
                                                href={website}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                {member.website}
                                            </SmartLink>
                                        </dd>
                                    </div>
                                ) : null}
                            </dl>
                        </div>
                        {member.logo ? (
                            <SmartLink
                                className="dlf-member-detail-head__logo"
                                href={memberHref}
                                aria-label={`Bekijk het profiel van ${member.title}`}
                            >
                                <CommunityImage asset={member.logo} title="" />
                            </SmartLink>
                        ) : null}
                    </div>
                </section>

                <section
                    className="dlf-community-section dlf-community-section--grid dlf-divider-section"
                    aria-label="Stage-informatie"
                >
                    <div className="dlf-community-grid-2 dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--desktop-leading-owns-rule dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        <article className="dlf-community-cell">
                            <h2 className="dlf-community-heading">Over deze stage</h2>
                            {community.descriptionHtml ? (
                                <div
                                    id="content"
                                    className="content dlf-community-copy"
                                    data-cms-html
                                    dangerouslySetInnerHTML={{ __html: community.descriptionHtml }}
                                />
                            ) : null}
                            {community.applyUrl ? (
                                <div className="mt-8">
                                    <DlfButtonLink
                                        href={community.applyUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        face="red"
                                        shadow="red"
                                    >
                                        Bekijk stage vacatures
                                    </DlfButtonLink>
                                </div>
                            ) : null}
                        </article>

                        <aside
                            className="dlf-community-cell dlf-member-detail-sidebar dlf-internship-sidebar"
                            aria-label="Contact en bedrijfsprofiel"
                        >
                            {hasContact && contact ? (
                                <section
                                    className="dlf-internship-sidebar__contact"
                                    aria-labelledby="internship-contact-heading"
                                >
                                    <h3
                                        id="internship-contact-heading"
                                        className="dlf-community-heading"
                                    >
                                        Stage contactpersoon
                                    </h3>
                                    <dl className="dlf-detail-properties">
                                        {contact.name ? (
                                            <div>
                                                <dt className="dlf-community-kicker mb-1!">Naam</dt>
                                                <dd className="m-0 text-base text-tertiary-dark">
                                                    {contact.name}
                                                </dd>
                                            </div>
                                        ) : null}
                                        {contact.email ? (
                                            <div>
                                                <dt className="dlf-community-kicker mb-1!">
                                                    E-mail
                                                </dt>
                                                <dd className="m-0 text-base">
                                                    <SmartLink href={`mailto:${contact.email}`}>
                                                        {contact.email}
                                                    </SmartLink>
                                                </dd>
                                            </div>
                                        ) : null}
                                        {contact.phone ? (
                                            <div>
                                                <dt className="dlf-community-kicker mb-1!">
                                                    Telefoonnummer
                                                </dt>
                                                <dd className="m-0 text-base">
                                                    <SmartLink
                                                        href={`tel:${phoneUrl(contact.phone)}`}
                                                    >
                                                        {contact.phone}
                                                    </SmartLink>
                                                </dd>
                                            </div>
                                        ) : null}
                                    </dl>
                                    <div className="dlf-internship-sidebar__action">
                                        <DlfButtonLink
                                            href={memberHref}
                                            face="outline-red"
                                            shadow="red"
                                        >
                                            Bekijk bedrijfsprofiel
                                        </DlfButtonLink>
                                    </div>
                                </section>
                            ) : (
                                <div className="dlf-internship-sidebar__action dlf-internship-sidebar__action--standalone">
                                    <DlfButtonLink
                                        href={memberHref}
                                        face="outline-red"
                                        shadow="red"
                                    >
                                        Bekijk bedrijfsprofiel
                                    </DlfButtonLink>
                                </div>
                            )}
                        </aside>
                    </div>
                </section>
            </div>
        </SiteLayout>
    );
}
