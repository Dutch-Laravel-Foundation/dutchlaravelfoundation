let initialization: Promise<void> | undefined;

const embedSource = "https://dlf.vragen.ai/embed.js?deployment=popup";
const initialQuestionPlaceholder = "Waar ben je naar op zoek?";

function loadEmbed() {
    return new Promise<void>((resolve, reject) => {
        const existing = document.querySelector<HTMLScriptElement>(`script[src="${embedSource}"]`);

        if (existing?.dataset.loaded === "true") {
            resolve();
            return;
        }

        const script = existing ?? document.createElement("script");
        script.src = embedSource;
        script.async = true;
        script.addEventListener(
            "load",
            () => {
                script.dataset.loaded = "true";
                resolve();
            },
            { once: true },
        );
        script.addEventListener("error", () => reject(new Error("Vragen.ai failed to load")), {
            once: true,
        });

        if (!existing) {
            document.head.append(script);
        }
    });
}

function watchPopup() {
    let lastTrigger: HTMLElement | null = null;
    let popupWasOpen = document.body.classList.contains("vragenai-popup-open");
    const popupRoot = document.querySelector<HTMLElement>("#vragenai-app");

    const decoratePopup = () => {
        const popup = popupRoot?.querySelector<HTMLElement>(".vragenai-popup");
        const header = popup?.querySelector<HTMLElement>(".vragenai-popup__header");

        if (!popup || !header) {
            return;
        }

        if (!header.querySelector(".dlf-vragenai-title")) {
            const title = document.createElement("h2");
            title.className = "dlf-vragenai-title";
            title.textContent = "Zoeken";
            popup.setAttribute("aria-label", title.textContent);
            header.prepend(title);
        }

        const questionInput = popup.querySelector<HTMLInputElement>(
            ".vragenai-question-form__input",
        );

        if (questionInput && !popup.querySelector(".vragenai-app--has-thread")) {
            questionInput.placeholder = initialQuestionPlaceholder;
        }
    };

    decoratePopup();

    if (popupRoot) {
        new MutationObserver(decoratePopup).observe(popupRoot, {
            childList: true,
            subtree: true,
        });
    }

    document.addEventListener(
        "click",
        (event) => {
            if (event.target instanceof Element) {
                lastTrigger = event.target.closest<HTMLElement>(".js-vragenai-trigger");
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
                        ? document.querySelector<HTMLElement>(".dlf-mobile-toggle")
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

export function initVragenAiSearch() {
    initialization ??= loadEmbed().then(watchPopup);

    return initialization;
}
