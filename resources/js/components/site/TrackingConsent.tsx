import { type RefObject } from "react";

import { type TrackingConsentVisualState } from "@/hooks/useTrackingConsent";

import { DlfButton } from "../ui/DlfButton";
import { SmartLink } from "../ui/SmartLink";

type TrackingConsentProps = {
    bannerRef: RefObject<HTMLElement | null>;
    onAccept: () => void;
    onReject: () => void;
    rendered: boolean;
    visualState: TrackingConsentVisualState;
};

export function TrackingConsent({
    bannerRef,
    onAccept,
    onReject,
    rendered,
    visualState,
}: TrackingConsentProps) {
    return (
        <section
            ref={bannerRef}
            className="dlf-tracking-consent"
            data-tracking-consent-banner
            data-visual-state={visualState}
            aria-labelledby="tracking-consent-title"
            aria-hidden={visualState === "closed"}
            tabIndex={-1}
            hidden={!rendered}
        >
            <div className="dlf-tracking-consent__inner">
                <div className="dlf-tracking-consent__copy">
                    <span className="dlf-tracking-consent__eyebrow">Cookies &amp; privacy</span>
                    <h2 id="tracking-consent-title">Kies voor een optimale ervaring</h2>
                    <p>
                        We gebruiken externe tools om jouw ervaring op onze website te verbeteren.
                        Lees meer in onze{" "}
                        <SmartLink href="/privacy-statement">
                            privacy- en cookieverklaring
                        </SmartLink>
                        .
                    </p>
                </div>

                <div className="dlf-tracking-consent__actions">
                    <DlfButton
                        className="dlf-tracking-consent__button dlf-tracking-consent__button--accept"
                        face="red"
                        shadow="red"
                        type="button"
                        data-tracking-consent-accept
                        onClick={onAccept}
                    >
                        Accepteren
                    </DlfButton>
                    <DlfButton
                        className="dlf-tracking-consent__button dlf-tracking-consent__button--reject"
                        face="outline-white"
                        shadow="white"
                        type="button"
                        data-tracking-consent-reject
                        aria-label="Doorgaan zonder tracking"
                        onClick={onReject}
                    >
                        <svg
                            className="dlf-tracking-consent__reject-icon"
                            viewBox="0 0 16 16"
                            aria-hidden="true"
                        >
                            <path d="m3 3 10 10M13 3 3 13" />
                        </svg>
                    </DlfButton>
                </div>
            </div>
        </section>
    );
}
