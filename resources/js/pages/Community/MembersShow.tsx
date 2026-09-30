import { CommunityImage } from "@/components/community-react/CommunityImage";
import { EmbedConsent } from "@/components/community-react/CommunityContentBlocks";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { truncate } from "@/components/editorial-react/format";
import { SiteLayout } from "@/components/site";
import { DlfButtonLink } from "@/components/ui/DlfButton";
import { SmartLink } from "@/components/ui/SmartLink";

type MembersShowProps = {
    community: App.Data.Community.MemberData;
    site: App.Data.SiteShell.SiteShellData;
};

function externalUrl(value: string): string {
    return /^https?:\/\//i.test(value) ? value : `https://${value}`;
}

function phoneUrl(value: string): string {
    return value.replace("(0)", "").replace(/[^+0-9]/g, "");
}

function embedVideoUrl(value: string): string | null {
    try {
        const url = new URL(value);

        if (url.hostname === "youtu.be") {
            return `https://www.youtube-nocookie.com/embed/${url.pathname.slice(1)}`;
        }

        if (url.hostname.includes("youtube.com")) {
            const id = url.searchParams.get("v") ?? url.pathname.split("/").filter(Boolean).at(-1);

            return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
        }

        if (url.hostname === "vimeo.com" || url.hostname.endsWith(".vimeo.com")) {
            const id = url.pathname.split("/").filter(Boolean).at(-1);

            return id ? `https://player.vimeo.com/video/${id}` : null;
        }
    } catch {
        return null;
    }

    return null;
}

function MemberVideo({ title, value }: { title: string; value: string }) {
    const embedded = embedVideoUrl(value);

    if (!embedded) {
        return (
            <div className="dlf-consent-embed relative mb-8 aspect-video overflow-hidden bg-black">
                <video
                    className="absolute inset-0 h-full w-full"
                    src={value}
                    aria-label={`Video van ${title}`}
                    controls
                    preload="metadata"
                />
            </div>
        );
    }

    const separator = embedded.includes("?") ? "&" : "?";
    const source = embedded.includes("youtube")
        ? `${embedded}${separator}rel=0&modestbranding=1`
        : `${embedded}${separator}byline=0&color=ff2d20&title=0&transparent=1`;

    return (
        <div className="dlf-consent-embed relative mb-8 aspect-video overflow-hidden bg-black">
            <iframe
                className="absolute inset-0 h-full w-full"
                data-consent-src={source}
                title={`Video van ${title}`}
                referrerPolicy="strict-origin-when-cross-origin"
                allow="autoplay; fullscreen; picture-in-picture; clipboard-write"
                allowFullScreen
                loading="lazy"
                hidden
            />
            <EmbedConsent />
        </div>
    );
}

export default function MembersShow({ community, site }: MembersShowProps) {
    const website = community.website ? externalUrl(community.website) : null;
    const recruitmentWebsite = community.recruitmentWebsite
        ? externalUrl(community.recruitmentWebsite)
        : null;

    return (
        <SiteLayout data={site} pageSlug={community.slug}>

            <div className="dlf-community-page dlf-member-detail-page" data-dlf-footer-cta-stage>
                <section
                    className="dlf-community-section dlf-divider-section"
                    aria-labelledby="member-heading"
                >
                    <Breadcrumb href="/leden" label="Leden" current={community.title} />
                    <div
                        className={`dlf-community-head dlf-member-detail-head dlf-divider-split${community.logo ? " dlf-member-detail-head--with-logo" : ""}`}
                    >
                        <div className="dlf-member-detail-head__copy">
                            <span className="dlf-community-kicker">{community.type ?? "Lid"}</span>
                            <h1 id="member-heading" className="dlf-community-title">
                                {community.title}
                            </h1>
                        </div>
                        {community.logo ? (
                            <div className="dlf-member-detail-head__logo" aria-hidden="true">
                                <CommunityImage asset={community.logo} title="" />
                            </div>
                        ) : null}
                    </div>
                </section>

                <section
                    className="dlf-community-section dlf-community-section--grid dlf-divider-section"
                    aria-label="Bedrijfsprofiel"
                >
                    <div className="dlf-community-grid-2 dlf-divider-grid dlf-divider-grid--desktop-2 dlf-divider-grid--desktop-leading-owns-rule dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                        <article className="dlf-community-cell">
                            <h2 className="dlf-community-heading">Over</h2>
                            {community.video ? (
                                <MemberVideo title={community.title} value={community.video} />
                            ) : null}
                            {community.descriptionHtml ? (
                                <div
                                    className="content dlf-community-copy"
                                    data-cms-html
                                    dangerouslySetInnerHTML={{ __html: community.descriptionHtml }}
                                />
                            ) : null}
                        </article>

                        <aside
                            className="dlf-community-cell dlf-member-detail-sidebar"
                            aria-labelledby="member-details-heading"
                        >
                            <h2 id="member-details-heading" className="dlf-community-heading">
                                Bedrijfsgegevens
                            </h2>
                            <dl className="dlf-detail-properties">
                                {community.city || community.province ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">Locatie</dt>
                                        <dd className="m-0 text-base text-tertiary-dark">
                                            {[community.city, community.province]
                                                .filter(Boolean)
                                                .join(", ")}
                                        </dd>
                                    </div>
                                ) : null}
                                {website && community.website ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">Website</dt>
                                        <dd className="m-0 text-base">
                                            <SmartLink
                                                href={website}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                {community.website}
                                            </SmartLink>
                                        </dd>
                                    </div>
                                ) : null}
                                {recruitmentWebsite && community.recruitmentWebsite ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">Vacatures</dt>
                                        <dd className="m-0 text-base">
                                            <SmartLink
                                                href={recruitmentWebsite}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                {community.recruitmentWebsite}
                                            </SmartLink>
                                        </dd>
                                    </div>
                                ) : null}
                                {community.email ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">E-mail</dt>
                                        <dd className="m-0 text-base">
                                            <SmartLink href={`mailto:${community.email}`}>
                                                {community.email}
                                            </SmartLink>
                                        </dd>
                                    </div>
                                ) : null}
                                {community.phone ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">
                                            Telefoonnummer
                                        </dt>
                                        <dd className="m-0 text-base">
                                            <SmartLink href={`tel:${phoneUrl(community.phone)}`}>
                                                {community.phone}
                                            </SmartLink>
                                        </dd>
                                    </div>
                                ) : null}
                                {community.employeeRange ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">
                                            Aantal medewerkers
                                        </dt>
                                        <dd className="m-0 text-base text-tertiary-dark">
                                            {community.employeeRange}
                                        </dd>
                                    </div>
                                ) : null}
                                {community.sbb ? (
                                    <div>
                                        <dt className="dlf-community-kicker mb-1!">Erkenning</dt>
                                        <dd className="m-0 text-base text-tertiary-dark">
                                            SBB erkend leerbedrijf
                                        </dd>
                                    </div>
                                ) : null}
                            </dl>

                            {website || recruitmentWebsite ? (
                                <div className="mt-10 flex flex-wrap gap-5">
                                    {website ? (
                                        <DlfButtonLink
                                            href={website}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            face="red"
                                            shadow="red"
                                        >
                                            Bezoek website
                                        </DlfButtonLink>
                                    ) : null}
                                    {recruitmentWebsite ? (
                                        <DlfButtonLink
                                            href={recruitmentWebsite}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            face="outline-red"
                                            shadow="red"
                                        >
                                            Bekijk vacatures
                                        </DlfButtonLink>
                                    ) : null}
                                </div>
                            ) : null}
                        </aside>
                    </div>
                </section>

                {community.internships.length ? (
                    <section
                        className="dlf-community-section dlf-member-detail-internships dlf-divider-section"
                        aria-labelledby="member-internships-heading"
                    >
                        <div className="dlf-community-head dlf-community-head--compact">
                            <span className="dlf-community-kicker">Stagebank</span>
                            <h2 id="member-internships-heading" className="dlf-community-heading">
                                Beschikbare stages bij {community.title}
                            </h2>
                        </div>
                        <div className="dlf-members-grid dlf-fill-grid dlf-divider-region dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-2 dlf-divider-grid--mobile-1">
                            {community.internships.map((internship) => {
                                const href =
                                    internship.url ??
                                    internship.uri ??
                                    `/stagebank/${internship.slug}`;

                                return (
                                    <SmartLink
                                        className="dlf-member-card"
                                        href={href}
                                        key={internship.id}
                                    >
                                        <span className="dlf-member-card__logo">
                                            {community.logo ? (
                                                <CommunityImage
                                                    asset={community.logo}
                                                    title={community.title}
                                                />
                                            ) : null}
                                        </span>
                                        <span className="dlf-member-card__body">
                                            <span className="dlf-member-card__name">
                                                {internship.title}
                                            </span>
                                            {internship.descriptionHtml ? (
                                                <span className="dlf-member-card__description">
                                                    {truncate(internship.descriptionHtml, 150)}
                                                </span>
                                            ) : null}
                                            <span className="dlf-member-card__location dlf-member-card__action">
                                                Bekijk stage
                                            </span>
                                        </span>
                                    </SmartLink>
                                );
                            })}
                            <span className="dlf-divider-grid__filler" aria-hidden="true" />
                        </div>
                    </section>
                ) : null}

                {community.cases.length ? (
                    <section
                        className="dlf-community-section dlf-community-section--grid dlf-member-detail-cases dlf-divider-section"
                        aria-labelledby="member-cases-heading"
                    >
                        <div className="dlf-community-head dlf-community-head--compact">
                            <span className="dlf-community-kicker">Cases</span>
                            <h2 id="member-cases-heading" className="dlf-community-heading">
                                Werk van {community.title}
                            </h2>
                        </div>
                        <div className="dlf-member-cases-grid dlf-fill-grid dlf-divider-region dlf-divider-grid dlf-divider-grid--fill dlf-divider-grid--desktop-2 dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1">
                            {community.cases.map((item) => {
                                const href = item.url ?? item.uri ?? `/cases/${item.slug}`;

                                return (
                                    <article className="dlf-community-cell" key={item.id}>
                                        {item.featuredImage ? (
                                            <SmartLink
                                                className="mb-6 block overflow-hidden"
                                                href={href}
                                                aria-label={`Bekijk ${item.displayTitle}`}
                                                data-progressive-media-frame
                                            >
                                                <CommunityImage
                                                    asset={item.featuredImage}
                                                    className="aspect-[4/3] w-full object-cover"
                                                    title={item.displayTitle}
                                                />
                                            </SmartLink>
                                        ) : null}
                                        <h3 className="dlf-community-heading dlf-community-heading--small">
                                            <SmartLink href={href}>{item.displayTitle}</SmartLink>
                                        </h3>
                                        {item.introductionHtml ? (
                                            <p className="dlf-community-copy">
                                                {truncate(item.introductionHtml, 180)}
                                            </p>
                                        ) : null}
                                        <SmartLink className="dlf-community-text-link" href={href}>
                                            Bekijk case <span aria-hidden="true">→</span>
                                        </SmartLink>
                                    </article>
                                );
                            })}
                            <span className="dlf-divider-grid__filler" aria-hidden="true" />
                        </div>
                    </section>
                ) : null}
            </div>
        </SiteLayout>
    );
}
