let initialization;

const loadEmbed = () =>
    new Promise((resolve, reject) => {
        const existing = document.querySelector(
            'script[src="https://dlf.vragen.ai/embed.js?deployment=popup"]',
        );

        if (existing?.dataset.loaded === "true") {
            resolve();
            return;
        }

        const script = existing || document.createElement("script");
        script.src = "https://dlf.vragen.ai/embed.js?deployment=popup";
        script.async = true;
        script.addEventListener(
            "load",
            () => {
                script.dataset.loaded = "true";
                resolve();
            },
            { once: true },
        );
        script.addEventListener("error", reject, { once: true });

        if (!existing) {
            document.head.append(script);
        }
    });

const watchPopup = () => {
    let lastTrigger = null;
    let popupWasOpen = document.body.classList.contains("vragenai-popup-open");

    document.addEventListener(
        "click",
        (event) => {
            const trigger =
                event.target instanceof Element
                    ? event.target.closest(".js-vragenai-trigger")
                    : null;

            if (trigger) {
                lastTrigger = trigger;
            }
        },
        true,
    );

    new MutationObserver(() => {
        const popupIsOpen = document.body.classList.contains("vragenai-popup-open");

        if (popupWasOpen && !popupIsOpen) {
            window.dispatchEvent(new CustomEvent("close-vragen-ai"));
            window.requestAnimationFrame(() => {
                const focusTarget =
                    lastTrigger?.offsetParent === null
                        ? document.querySelector(".dlf-mobile-toggle")
                        : lastTrigger;

                focusTarget?.focus();
            });
        }

        popupWasOpen = popupIsOpen;
    }).observe(document.body, {
        attributes: true,
        attributeFilter: ["class"],
    });
};

export function initVragenAiSearch() {
    initialization ??= loadEmbed().then(watchPopup);

    return initialization;
}
