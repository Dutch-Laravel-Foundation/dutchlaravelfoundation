const SELECTOR = "[data-lid-benefits-marquee]";
const AUTOPLAY_SPEED = 22;
const KEYBOARD_STEP = 160;

const disableCloneInteractions = (clone) => {
    clone.setAttribute("aria-hidden", "true");

    clone.querySelectorAll("a, button, input, select, textarea, [tabindex]").forEach((element) => {
        element.setAttribute("tabindex", "-1");
    });
};

const initializeBenefitsMarquee = (marquee) => {
    const originals = Array.from(marquee.children);

    if (originals.length < 2) {
        return;
    }

    const before = originals.map((item) => item.cloneNode(true));
    const after = originals.map((item) => item.cloneNode(true));

    before.forEach(disableCloneInteractions);
    after.forEach(disableCloneInteractions);

    marquee.prepend(...before);
    marquee.append(...after);
    marquee.tabIndex = 0;

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let cycleWidth = 0;
    let frame = null;
    let lastTimestamp = null;
    let autoplayPosition = 0;
    let wasAutoplaying = false;
    let isHovered = false;
    let hasFocus = false;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartScrollLeft = 0;

    const measure = (preservePosition = false) => {
        const previousCycleWidth = cycleWidth;
        const previousOffset = previousCycleWidth
            ? (marquee.scrollLeft - previousCycleWidth) / previousCycleWidth
            : 0;

        cycleWidth = marquee.scrollWidth / 3;
        marquee.scrollLeft = preservePosition
            ? cycleWidth + (previousOffset * cycleWidth)
            : cycleWidth;
        autoplayPosition = marquee.scrollLeft;
    };

    const normalizePosition = () => {
        if (!cycleWidth) {
            return;
        }

        if (marquee.scrollLeft < cycleWidth * .25) {
            marquee.scrollLeft += cycleWidth;
        } else if (marquee.scrollLeft > cycleWidth * 1.75) {
            marquee.scrollLeft -= cycleWidth;
        }

        autoplayPosition = marquee.scrollLeft;
    };

    const shouldAutoplay = () => (
        !reducedMotion.matches
        && !isHovered
        && !hasFocus
        && !isDragging
        && !document.hidden
    );

    const animate = (timestamp) => {
        const isAutoplaying = shouldAutoplay();

        if (isAutoplaying && !wasAutoplaying) {
            autoplayPosition = marquee.scrollLeft;
        }

        if (isAutoplaying && lastTimestamp !== null) {
            const elapsed = Math.min(timestamp - lastTimestamp, 50);
            autoplayPosition -= AUTOPLAY_SPEED * (elapsed / 1000);

            if (autoplayPosition < cycleWidth * .25) {
                autoplayPosition += cycleWidth;
            }

            marquee.scrollLeft = autoplayPosition;
        }

        wasAutoplaying = isAutoplaying;
        lastTimestamp = timestamp;
        frame = window.requestAnimationFrame(animate);
    };

    const stopDragging = (event) => {
        if (!isDragging) {
            return;
        }

        isDragging = false;

        if (marquee.hasPointerCapture?.(event.pointerId)) {
            marquee.releasePointerCapture(event.pointerId);
        }

        normalizePosition();
    };

    marquee.addEventListener("mouseenter", () => {
        isHovered = true;
    });

    marquee.addEventListener("mouseleave", () => {
        isHovered = false;
    });

    marquee.addEventListener("focusin", () => {
        hasFocus = true;
    });

    marquee.addEventListener("focusout", (event) => {
        hasFocus = marquee.contains(event.relatedTarget);
    });

    marquee.addEventListener("pointerdown", (event) => {
        if (event.button !== 0) {
            return;
        }

        isDragging = true;

        if (event.pointerType !== "mouse") {
            return;
        }

        dragStartX = event.clientX;
        dragStartScrollLeft = marquee.scrollLeft;
        marquee.setPointerCapture(event.pointerId);
        event.preventDefault();
    });

    marquee.addEventListener("pointermove", (event) => {
        if (!isDragging) {
            return;
        }

        marquee.scrollLeft = dragStartScrollLeft - (event.clientX - dragStartX);
        normalizePosition();
    });

    marquee.addEventListener("pointerup", stopDragging);
    marquee.addEventListener("pointercancel", stopDragging);

    marquee.addEventListener("scroll", () => {
        if (!shouldAutoplay()) {
            normalizePosition();
        }
    }, { passive: true });

    marquee.addEventListener("keydown", (event) => {
        if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") {
            return;
        }

        event.preventDefault();
        marquee.scrollBy({
            left: event.key === "ArrowLeft" ? -KEYBOARD_STEP : KEYBOARD_STEP,
            behavior: reducedMotion.matches ? "auto" : "smooth",
        });
    });

    const resizeObserver = new ResizeObserver(() => measure(true));
    resizeObserver.observe(marquee);

    measure();
    frame = window.requestAnimationFrame(animate);

    window.addEventListener("pagehide", () => {
        window.cancelAnimationFrame(frame);
        resizeObserver.disconnect();
    }, { once: true });
};

document.querySelectorAll(SELECTOR).forEach(initializeBenefitsMarquee);
