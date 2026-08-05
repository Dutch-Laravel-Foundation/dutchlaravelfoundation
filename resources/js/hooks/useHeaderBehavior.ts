import Headroom from "headroom.js";
import { router } from "@inertiajs/react";
import { type RefObject, useEffect } from "react";

type ScheduleFrame = (callback: FrameRequestCallback) => number;
type CancelFrame = (handle: number) => void;

type ScrollJumpWindow = {
    addEventListener: (
        type: "scroll",
        listener: EventListener,
        options?: AddEventListenerOptions | boolean,
    ) => void;
    removeEventListener: (type: "scroll", listener: EventListener) => void;
    setTimeout: (callback: () => void, delay: number) => number;
    clearTimeout: (handle: number) => void;
};

type ScrollJumpOptions = {
    browserWindow?: ScrollJumpWindow;
    scheduleFrame?: ScheduleFrame;
    cancelFrame?: CancelFrame;
};

export function shouldRevealHeaderForVisit(visit: { prefetch: boolean }) {
    return !visit.prefetch;
}

export function isSameDocumentAnchor(href: string, currentHref: string) {
    try {
        const current = new URL(currentHref);
        const destination = new URL(href, current);

        return (
            destination.hash.length > 1 &&
            destination.origin === current.origin &&
            destination.pathname === current.pathname &&
            destination.search === current.search
        );
    } catch {
        return false;
    }
}

export function preserveHeaderDuringScrollJump(
    headroom: Pick<Headroom, "freeze" | "unfreeze">,
    {
        browserWindow = window,
        scheduleFrame = requestAnimationFrame,
        cancelFrame = cancelAnimationFrame,
    }: ScrollJumpOptions = {},
) {
    let firstFrame: number | null = null;
    let secondFrame: number | null = null;
    let fallbackTimer: number | null = null;
    let released = false;

    const release = () => {
        if (released) {
            return;
        }

        released = true;

        if (firstFrame !== null) {
            cancelFrame(firstFrame);
        }

        if (secondFrame !== null) {
            cancelFrame(secondFrame);
        }

        if (fallbackTimer !== null) {
            browserWindow.clearTimeout(fallbackTimer);
        }

        browserWindow.removeEventListener("scroll", queueRelease);
        headroom.unfreeze();
    };
    const queueRelease = () => {
        if (firstFrame !== null) {
            cancelFrame(firstFrame);
        }

        if (secondFrame !== null) {
            cancelFrame(secondFrame);
        }

        firstFrame = scheduleFrame(() => {
            firstFrame = null;
            secondFrame = scheduleFrame(release);
        });
    };

    headroom.freeze();
    browserWindow.addEventListener("scroll", queueRelease, { passive: true });
    fallbackTimer = browserWindow.setTimeout(release, 500);

    return release;
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

        if (!header) {
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
        let stopPreservingScrollJump: (() => void) | null = null;
        const preserveCurrentHeaderState = () => {
            stopPreservingScrollJump?.();
            stopPreservingScrollJump = preserveHeaderDuringScrollJump(headroom);
        };

        const handleAnchorClick = (event: MouseEvent) => {
            if (
                event.defaultPrevented ||
                event.button !== 0 ||
                event.altKey ||
                event.ctrlKey ||
                event.metaKey ||
                event.shiftKey ||
                !(event.target instanceof Element)
            ) {
                return;
            }

            const anchor = event.target.closest("a[href]");

            if (
                !(anchor instanceof HTMLAnchorElement) ||
                anchor.hasAttribute("download") ||
                (anchor.target && anchor.target !== "_self") ||
                !isSameDocumentAnchor(anchor.href, window.location.href)
            ) {
                return;
            }

            preserveCurrentHeaderState();
        };
        const handleInvalid = () => {
            preserveCurrentHeaderState();
        };

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
        document.addEventListener("click", handleAnchorClick);
        document.addEventListener("invalid", handleInvalid, true);
        updateStickyOffset();

        return () => {
            globalThis.clearTimeout(initializeHeadroom);

            if (initializedAt !== null) {
                const remainingInitializationTime = Math.max(0, 110 - (Date.now() - initializedAt));

                globalThis.setTimeout(() => headroom.destroy(), remainingInitializationTime);
            }

            headerStateObserver.disconnect();
            headerSizeObserver.disconnect();
            document.removeEventListener("click", handleAnchorClick);
            document.removeEventListener("invalid", handleInvalid, true);
            stopListeningForNavigation();
            stopPreservingScrollJump?.();

            if (navigationFrame !== null) {
                cancelAnimationFrame(navigationFrame);
            }

            header.classList.remove("dlf-header--instant");
            document.documentElement.style.removeProperty("--dlf-header-visible-height");
            document.documentElement.style.removeProperty("--dlf-sticky-top");
        };
    }, [headerRef]);
}
