const SELECTOR = "[data-lid-benefits-marquee]";
const KEYBOARD_STEP = 320;
const SINGLE_ROW_QUERY = "(max-width: 1023px)";

export const initializeBenefitsScroller = (scroller) => {
    if (scroller.children.length < 2) {
        return;
    }

    scroller.tabIndex = 0;
    scroller.scrollLeft = window.matchMedia(SINGLE_ROW_QUERY).matches
        ? 0
        : Math.max(0, scroller.scrollWidth - scroller.clientWidth);

    const updateFadeState = () => {
        const maxScroll = Math.max(0, scroller.scrollWidth - scroller.clientWidth);

        scroller.classList.toggle("can-scroll-left", scroller.scrollLeft > 1);
        scroller.classList.toggle("can-scroll-right", scroller.scrollLeft < maxScroll - 1);
    };

    updateFadeState();
    scroller.addEventListener("scroll", updateFadeState, { passive: true });

    let isDragging = false;
    let dragStartX = 0;
    let dragStartScrollLeft = 0;

    const stopDragging = (event) => {
        if (!isDragging) {
            return;
        }

        isDragging = false;

        if (scroller.hasPointerCapture?.(event.pointerId)) {
            scroller.releasePointerCapture(event.pointerId);
        }
    };

    scroller.addEventListener("pointerdown", (event) => {
        if (event.button !== 0 || event.pointerType !== "mouse") {
            return;
        }

        isDragging = true;
        dragStartX = event.clientX;
        dragStartScrollLeft = scroller.scrollLeft;
        scroller.setPointerCapture(event.pointerId);
    });

    scroller.addEventListener("pointermove", (event) => {
        if (!isDragging) {
            return;
        }

        scroller.scrollLeft = dragStartScrollLeft - (event.clientX - dragStartX);
    });

    scroller.addEventListener("pointerup", stopDragging);
    scroller.addEventListener("pointercancel", stopDragging);

    scroller.addEventListener("keydown", (event) => {
        if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") {
            return;
        }

        event.preventDefault();
        scroller.scrollBy({
            left: event.key === "ArrowLeft" ? -KEYBOARD_STEP : KEYBOARD_STEP,
            behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches
                ? "auto"
                : "smooth",
        });
    });
};

document.querySelectorAll(SELECTOR).forEach(initializeBenefitsScroller);
