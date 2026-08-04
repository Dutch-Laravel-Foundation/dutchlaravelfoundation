import Headroom from "headroom.js";
import { router } from "@inertiajs/react";
import { type RefObject, useEffect } from "react";

type ScheduleFrame = (callback: FrameRequestCallback) => number;

export function shouldRevealHeaderForVisit(visit: { prefetch: boolean }) {
    return !visit.prefetch;
}

export function revealHeaderForNavigation(
    header: HTMLElement,
    headroom: Pick<Headroom, "pin">,
    scheduleFrame: ScheduleFrame = requestAnimationFrame,
) {
    header.classList.add("dlf-header--instant");
    headroom.pin();
    header.getBoundingClientRect();

    return scheduleFrame(() => header.classList.remove("dlf-header--instant"));
}

export function useHeaderBehavior(headerRef: RefObject<HTMLElement | null>) {
    useEffect(() => {
        const header = headerRef.current;

        if (!header || "documentMode" in document) {
            return;
        }

        const offset = {
            down: header.offsetHeight,
            up: header.offsetHeight,
        };
        const headroom = new Headroom(header, {
            offset,
            tolerance: {
                up: 5,
                down: 0,
            },
            classes: {
                initial: "animated",
                pinned: "slideDown",
                unpinned: "slideUp",
            },
        });
        const updateStickyOffset = () => {
            const headerHeight = header.classList.contains("slideUp") ? 0 : header.offsetHeight;

            document.documentElement.style.setProperty(
                "--dlf-header-visible-height",
                `${headerHeight}px`,
            );
            document.documentElement.style.setProperty(
                "--dlf-sticky-top",
                `${headerHeight + 24}px`,
            );
        };
        const headerStateObserver = new MutationObserver(updateStickyOffset);
        const headerSizeObserver = new ResizeObserver(() => {
            offset.down = header.offsetHeight;
            offset.up = header.offsetHeight;
            updateStickyOffset();
        });
        let navigationFrame: number | null = null;

        const stopListeningForNavigation = router.on("before", (event) => {
            if (!shouldRevealHeaderForVisit(event.detail.visit)) {
                return;
            }

            if (navigationFrame !== null) {
                cancelAnimationFrame(navigationFrame);
            }

            navigationFrame = revealHeaderForNavigation(header, headroom);
            updateStickyOffset();
        });

        let initializedAt: number | null = null;
        const initializeHeadroom = globalThis.setTimeout(() => {
            headroom.init();
            initializedAt = Date.now();
        });
        headerStateObserver.observe(header, {
            attributeFilter: ["class"],
            attributes: true,
        });
        headerSizeObserver.observe(header);
        updateStickyOffset();

        return () => {
            globalThis.clearTimeout(initializeHeadroom);

            if (initializedAt !== null) {
                const remainingInitializationTime = Math.max(0, 110 - (Date.now() - initializedAt));

                globalThis.setTimeout(() => headroom.destroy(), remainingInitializationTime);
            }

            headerStateObserver.disconnect();
            headerSizeObserver.disconnect();
            stopListeningForNavigation();

            if (navigationFrame !== null) {
                cancelAnimationFrame(navigationFrame);
            }

            header.classList.remove("dlf-header--instant");
            document.documentElement.style.removeProperty("--dlf-header-visible-height");
            document.documentElement.style.removeProperty("--dlf-sticky-top");
        };
    }, [headerRef]);
}
