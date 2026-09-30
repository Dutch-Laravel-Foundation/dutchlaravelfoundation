function PartnerLogo({
    partner,
    decorative = false,
}: {
    partner: App.Data.Home.PartnerData;
    decorative?: boolean;
}) {
    if (!partner.logo) {
        return null;
    }

    return (
        <img
            src={partner.logo.url}
            alt={decorative ? "" : (partner.logo.alt ?? partner.title)}
            width={partner.logo.width ?? undefined}
            height={partner.logo.height ?? undefined}
            loading="lazy"
            decoding="async"
        />
    );
}

export function PartnerMarquee({ partners }: { partners: App.Data.Home.PartnerData[] }) {
    const partnersWithLogos = partners.filter((partner) => partner.logo !== null);

    return (
        <section
            className="dlf-home-partners dlf-divider-section dlf-divider-section--full-bleed-top"
            aria-labelledby="partners-heading"
        >
            <div className="dlf-home-partners__spacer" aria-hidden="true" />
            <h2 id="partners-heading">Onze partners</h2>
            <div className="dlf-home-partners__marquee">
                <div className="dlf-home-partners__track">
                    <div className="dlf-home-partners__group">
                        {partnersWithLogos.map((partner) => (
                            <PartnerLogo partner={partner} key={partner.id} />
                        ))}
                    </div>
                    <div className="dlf-home-partners__group" aria-hidden="true">
                        {partnersWithLogos.map((partner) => (
                            <PartnerLogo partner={partner} decorative key={partner.id} />
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
