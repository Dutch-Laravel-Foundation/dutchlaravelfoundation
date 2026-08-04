import { useEffect } from "react";

export function useFooterCtaStage(enabled: boolean) {
    useEffect(() => {
        if (!enabled) {
            return;
        }

        const stage = document.querySelector<HTMLElement>("[data-dlf-footer-cta-stage]");
        const cta = document.querySelector<HTMLElement>(".dlf-footer .dlf-cta-section");
        const card = cta?.querySelector<HTMLElement>(".dlf-cta-card");

        if (!stage || !cta || !card) {
            return;
        }

        const syncStagePadding = () => {
            const sideInset = card.getBoundingClientRect().left - cta.getBoundingClientRect().left;
            const cardHalfHeight = card.getBoundingClientRect().height / 2;

            stage.style.setProperty(
                "--dlf-footer-cta-stage-padding",
                `${sideInset + cardHalfHeight}px`,
            );
        };
        const observer = new ResizeObserver(syncStagePadding);

        syncStagePadding();
        observer.observe(cta);
        observer.observe(card);

        return () => {
            observer.disconnect();
            stage.style.removeProperty("--dlf-footer-cta-stage-padding");
        };
    }, [enabled]);
}
