const searchTriggers = [...document.querySelectorAll(".js-vragenai-trigger")];

if (searchTriggers.length > 0) {
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
        true
    );

    new MutationObserver(() => {
        const popupIsOpen = document.body.classList.contains(
            "vragenai-popup-open"
        );

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
}
