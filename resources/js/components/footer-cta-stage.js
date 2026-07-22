export const initFooterCtaStage = () => {
    const stage = document.querySelector("[data-dlf-footer-cta-stage]");
    const cta = document.querySelector(".dlf-footer .dlf-cta-section");
    const card = cta?.querySelector(".dlf-cta-card");

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

    syncStagePadding();

    if ("ResizeObserver" in window) {
        const observer = new ResizeObserver(syncStagePadding);

        observer.observe(cta);
        observer.observe(card);
    }
};
