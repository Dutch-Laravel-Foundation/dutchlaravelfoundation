import { DlfButtonLink, type DlfButtonFace, type DlfButtonShadow } from "../ui/DlfButton";
import { CheckIcon } from "./icons";
import { type FooterCta, type FooterCtaButtonStyle } from "./types";

function ctaButtonAppearance(
    theme: string | null | undefined,
    style: string | null | undefined,
    primary: boolean,
): { face: DlfButtonFace; shadow: DlfButtonShadow } {
    if (theme === "dark") {
        if (primary && style === "primary") {
            return { face: "red", shadow: "red" };
        }

        if (style === "light-alt") {
            return { face: "outline-white", shadow: "white" };
        }

        if (!primary && style !== "dark" && style !== "light") {
            return { face: "red", shadow: "white" };
        }

        return { face: "white", shadow: "white" };
    }

    if (style === "dark" || style === "light-alt") {
        return { face: "outline-white", shadow: "white" };
    }

    return { face: "white", shadow: "white" };
}

type CtaLinkProps = {
    href: string;
    label: string;
    primary: boolean;
    style?: FooterCtaButtonStyle | null;
    theme?: FooterCta["theme"];
};

function CtaLink({ href, label, primary, style, theme }: CtaLinkProps) {
    const appearance = ctaButtonAppearance(theme?.value, style?.value, primary);

    return (
        <DlfButtonLink
            href={href}
            download={primary && href.endsWith(".pdf") ? true : undefined}
            face={appearance.face}
            shadow={appearance.shadow}
        >
            {label}
        </DlfButtonLink>
    );
}

export function FooterCtaBanner({ cta }: { cta: FooterCta }) {
    return (
        <section className="dlf-cta-section" aria-labelledby="footer-cta-title">
            <div
                className={
                    cta.theme?.value === "dark" ? "dlf-cta-card dlf-cta-card--dark" : "dlf-cta-card"
                }
            >
                <div className="dlf-cta-row">
                    <div className="dlf-cta-copy">
                        {cta.eyebrow ? <span className="dlf-eyebrow">{cta.eyebrow}</span> : null}

                        <h2 id="footer-cta-title">{cta.title}</h2>

                        {cta.benefits?.length ? (
                            <div className="dlf-cta-benefits">
                                {cta.benefits.map((benefit) => (
                                    <span key={benefit}>
                                        <CheckIcon />
                                        {benefit}
                                    </span>
                                ))}
                            </div>
                        ) : cta.description ? (
                            <div
                                className="dlf-cta-description"
                                data-cms-html
                                dangerouslySetInnerHTML={{ __html: cta.description }}
                            />
                        ) : null}
                    </div>

                    {cta.link?.url || cta.secondaryLink?.url ? (
                        <div className="dlf-cta-actions">
                            {cta.secondaryLink?.url && cta.secondaryButtonText ? (
                                <CtaLink
                                    href={cta.secondaryLink.url}
                                    label={cta.secondaryButtonText}
                                    primary={false}
                                    style={cta.secondaryButtonStyle}
                                    theme={cta.theme}
                                />
                            ) : null}
                            {cta.link?.url && cta.buttonText ? (
                                <CtaLink
                                    href={cta.link.url}
                                    label={cta.buttonText}
                                    primary
                                    style={cta.buttonStyle}
                                    theme={cta.theme}
                                />
                            ) : null}
                        </div>
                    ) : null}
                </div>
            </div>
        </section>
    );
}
