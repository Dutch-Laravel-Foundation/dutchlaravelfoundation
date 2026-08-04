import { Fragment } from "react";

import { cn } from "@/lib/utils";

import { SmartLink } from "../ui/SmartLink";
import { FooterCtaBanner } from "./FooterCta";
import { NewsletterForm } from "./NewsletterForm";
import {
    type FooterCta,
    type FooterMember,
    type NavigationItem,
    type NewsletterForm as NewsletterFormData,
    type SocialLink,
} from "./types";

type FooterProps = {
    cta?: FooterCta | null;
    legalNavigation: readonly NavigationItem[];
    members: readonly FooterMember[];
    newsletterForm: NewsletterFormData | null;
    pageSlug: string;
    settingsHidden: boolean;
    siteName: string;
    socials: readonly SocialLink[];
};

export function Footer({
    cta,
    legalNavigation,
    members,
    newsletterForm,
    pageSlug,
    settingsHidden,
    siteName,
    socials,
}: FooterProps) {
    return (
        <footer className={cn("dlf-footer", pageSlug === "home" && "dlf-footer--home")}>
            {cta ? <FooterCtaBanner cta={cta} /> : null}

            <div className={cn("dlf-footer-band", cta && "dlf-footer-band--with-cta")}>
                <div className="dlf-footer-inner">
                    <section className="dlf-footer-brand" aria-label={siteName}>
                        <h2>{siteName}</h2>
                        <div className="dlf-footer-recognized">
                            <span>Recognized by</span>
                            <span>
                                <img
                                    src="/assets/img/laravel-wordmark-white.svg"
                                    alt="Laravel"
                                    width="1280"
                                    height="314"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </span>
                        </div>
                    </section>

                    <section className="dlf-footer-content" aria-label="Footer informatie">
                        <div className="dlf-members-column">
                            <h3>Leden</h3>
                            <ul>
                                {members.map((member) => (
                                    <li key={member.id}>
                                        <SmartLink href={member.url ?? "#"}>
                                            {member.title}
                                        </SmartLink>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="dlf-footer-right">
                            <p>
                                De Dutch Laravel Foundation zorgt voor de promotie van Laravel en
                                stimuleert kennisuitwisseling tussen Laravel developers in
                                Nederland.
                            </p>

                            <div className="dlf-footer-socials">
                                {socials.map((social) =>
                                    social.link?.url && social.icon?.url ? (
                                        <SmartLink
                                            href={social.link.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label={social.title}
                                            key={social.id}
                                        >
                                            <img
                                                src={social.icon.url}
                                                alt=""
                                                width={social.icon.width ?? undefined}
                                                height={social.icon.height ?? undefined}
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        </SmartLink>
                                    ) : null,
                                )}
                            </div>

                            <section
                                className="dlf-newsletter"
                                aria-labelledby="footer-newsletter-title"
                            >
                                <h3 id="footer-newsletter-title">Aanmelden nieuwsbrief</h3>
                                <p>
                                    Zo&apos;n één keer per kwartaal sturen we een update met nieuws,
                                    events en verhalen uit de community.
                                </p>

                                {newsletterForm ? <NewsletterForm form={newsletterForm} /> : null}
                            </section>
                        </div>
                    </section>

                    <section className="dlf-footer-bottom" aria-label="Juridisch en partners">
                        <div>
                            <div className="dlf-legal-links">
                                {legalNavigation.map((item) => (
                                    <Fragment key={item.id}>
                                        <SmartLink href={item.url ?? item.permalink ?? "#"}>
                                            {item.title}
                                        </SmartLink>
                                        <span aria-hidden="true">|</span>
                                    </Fragment>
                                ))}
                                <button
                                    type="button"
                                    data-tracking-consent-settings
                                    hidden={settingsHidden}
                                >
                                    Cookievoorkeuren
                                </button>
                            </div>

                            <div className="dlf-footer-badges">
                                <SmartLink
                                    href="https://www.leadinfo.com/nl/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <img
                                        src="/assets/redesign/logos/leadinfo-240.webp"
                                        alt="Leadinfo"
                                        width="240"
                                        height="108"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </SmartLink>
                                <SmartLink
                                    href="https://www.larabelles.com/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <img
                                        src="/assets/redesign/logos/larabelles-badge-320.webp"
                                        alt="Larabelles"
                                        width="320"
                                        height="94"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </SmartLink>
                                <div className="dlf-hosted-by">
                                    <span>Hosted by</span>
                                    <SmartLink
                                        href="https://www.shockmedia.nl/"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <img
                                            src="/assets/redesign/logos/shockmedia-320.webp"
                                            alt="Shock Media"
                                            width="320"
                                            height="90"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                    </SmartLink>
                                </div>
                            </div>
                        </div>

                        <p>
                            &copy; {new Date().getFullYear()} - {siteName}
                        </p>
                    </section>
                </div>
            </div>
        </footer>
    );
}
