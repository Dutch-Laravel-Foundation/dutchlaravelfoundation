import { SmartLink } from "@/components/ui/SmartLink";

export function ContactCard({
    organization,
}: {
    organization: App.Data.SiteShell.OrganizationData;
}) {
    const telephone = organization.phone?.replace("(0)", "").replace(/[^+0-9]/g, "");

    return (
        <aside
            className="dlf-public-contact-card dlf-block"
            aria-label="Contactgegevens Dutch Laravel Foundation"
        >
            <span className="editorial-eyebrow">Contact</span>
            <h2>{organization.title}</h2>
            <p>
                {organization.address}
                <br />
                {organization.zipcode} {organization.city}
            </p>
            <p>
                {organization.phone ? (
                    <SmartLink href={`tel:${telephone}`}>{organization.phone}</SmartLink>
                ) : null}
                <br />
                {organization.email ? (
                    <SmartLink href={`mailto:${organization.email}`}>
                        {organization.email}
                    </SmartLink>
                ) : null}
            </p>
            <p>KVK: {organization.coc}</p>
        </aside>
    );
}
