import { useEffect } from "react";

export function useFooterCtaStage(enabled: boolean) {
    useEffect(() => {
        if (!enabled) {
            return;
        }

        let stagedElement: HTMLElement | null = null;

        const syncStagePadding = () => {
            const stage = document.querySelector<HTMLElement>("[data-dlf-footer-cta-stage]");
            const cta = document.querySelector<HTMLElement>(".dlf-footer .dlf-cta-section");
            const card = cta?.querySelector<HTMLElement>(".dlf-cta-card");

            if (!stage || !cta || !card) {
                return;
            }

            resizeObserver.observe(cta);
            resizeObserver.observe(card);

            if (stagedElement !== stage) {
                stagedElement?.style.removeProperty("--dlf-footer-cta-stage-padding");
                stagedElement = stage;
            }

            const sideInset = card.getBoundingClientRect().left - cta.getBoundingClientRect().left;
            const cardHalfHeight = card.getBoundingClientRect().height / 2;

            stage.style.setProperty(
                "--dlf-footer-cta-stage-padding",
                `${sideInset + cardHalfHeight}px`,
            );
        };
        const resizeObserver = new ResizeObserver(syncStagePadding);
        const pageObserver = new MutationObserver(syncStagePadding);
        const mainContent = document.querySelector("#main-content");

        syncStagePadding();

        if (mainContent) {
            pageObserver.observe(mainContent, { childList: true });
        }

        return () => {
            pageObserver.disconnect();
            resizeObserver.disconnect();
            stagedElement?.style.removeProperty("--dlf-footer-cta-stage-padding");
        };
    }, [enabled]);
}
