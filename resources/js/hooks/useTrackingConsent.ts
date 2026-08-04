import { type Reducer, type RefObject, useCallback, useEffect, useReducer, useRef } from "react";

export const TRACKING_CONSENT_STORAGE_KEY = "dlf_tracking_consent";
export const TRACKING_CONSENT_VERSION = 1;

type TrackingChoice = "accepted" | "rejected";
export type TrackingConsentVisualState = "closed" | "open";

type TrackingConsentState = {
    rendered: boolean;
    resolved: boolean;
    visualState: TrackingConsentVisualState;
};

type TrackingConsentAction =
    | { type: "initial-choice"; choice: TrackingChoice | null }
    | { type: "show" }
    | { type: "finish-show" }
    | { type: "hide" }
    | { type: "finish-hide" };

const initialTrackingConsentState: TrackingConsentState = {
    rendered: true,
    resolved: false,
    visualState: "open",
};
const validChoices = new Set<TrackingChoice>(["accepted", "rejected"]);
const trackingCookiePrefixes = ["_ga", "_gid", "_gat", "_gcl_", "_li_", "li_", "leadinfo"];
const trackingConsentMotionDuration = 200;

export const trackingConsentReducer: Reducer<TrackingConsentState, TrackingConsentAction> = (
    state = initialTrackingConsentState,
    action,
) => {
    switch (action.type) {
        case "initial-choice":
            return {
                rendered: action.choice === null,
                resolved: true,
                visualState: action.choice === null ? "open" : "closed",
            };
        case "show":
            return { ...state, rendered: true, visualState: "closed" };
        case "finish-show":
            return { ...state, visualState: "open" };
        case "hide":
            return { ...state, visualState: "closed" };
        case "finish-hide":
            return { ...state, rendered: false };
    }
};

function readConsent(storage: Storage): TrackingChoice | null {
    try {
        const consent = JSON.parse(storage.getItem(TRACKING_CONSENT_STORAGE_KEY) ?? "null") as {
            choice?: TrackingChoice;
            version?: number;
        } | null;

        if (
            consent?.version !== TRACKING_CONSENT_VERSION ||
            !consent.choice ||
            !validChoices.has(consent.choice)
        ) {
            return null;
        }

        return consent.choice;
    } catch {
        return null;
    }
}

function storeConsent(storage: Pick<Storage, "setItem">, choice: TrackingChoice) {
    storage.setItem(
        TRACKING_CONSENT_STORAGE_KEY,
        JSON.stringify({
            version: TRACKING_CONSENT_VERSION,
            choice,
        }),
    );
}

type ApplyTrackingChoiceOptions = {
    storage: Pick<Storage, "setItem">;
    startThirdParties: () => void;
    revokeThirdParties: () => void;
    scrollToTop?: () => void;
};

export function applyTrackingChoice(
    choice: TrackingChoice,
    { storage, startThirdParties, revokeThirdParties, scrollToTop }: ApplyTrackingChoiceOptions,
) {
    try {
        storeConsent(storage, choice);
    } catch {
        return false;
    }

    if (choice === "accepted") {
        startThirdParties();
        scrollToTop?.();
    } else {
        revokeThirdParties();
    }

    return true;
}

export function loadConsentEmbeds(documentRoot: Document = document) {
    documentRoot.querySelectorAll<HTMLElement>("[data-consent-src]").forEach((embed) => {
        const source = embed.getAttribute("data-consent-src");

        if (!source) {
            return;
        }

        if (embed.getAttribute("src") !== source) {
            embed.setAttribute("src", source);
        }

        embed.hidden = false;
    });

    documentRoot
        .querySelectorAll<HTMLElement>("[data-consent-placeholder], [data-consent-fallback]")
        .forEach((element) => {
            element.hidden = true;
        });

    documentRoot
        .querySelectorAll<HTMLElement>("[data-tracking-consent-embed-settings]")
        .forEach((control) => {
            control.hidden = true;
        });
}

export function revokeConsentEmbeds(
    documentRoot: Document = document,
    { showSettings = true }: { showSettings?: boolean } = {},
) {
    documentRoot.querySelectorAll<HTMLElement>("[data-consent-src]").forEach((embed) => {
        embed.removeAttribute("src");
        embed.hidden = true;
    });

    documentRoot
        .querySelectorAll<HTMLElement>("[data-consent-placeholder], [data-consent-fallback]")
        .forEach((element) => {
            element.hidden = false;
        });

    documentRoot
        .querySelectorAll<HTMLElement>("[data-tracking-consent-embed-settings]")
        .forEach((control) => {
            control.hidden = !showSettings;
        });
}

type ObserveConsentEmbedsOptions = {
    createObserver?: (
        callback: MutationCallback,
    ) => Pick<MutationObserver, "disconnect" | "observe">;
    documentRoot?: Document;
    getChoice?: () => TrackingChoice | null;
};

export function observeConsentEmbeds({
    createObserver = (callback) => new MutationObserver(callback),
    documentRoot = document,
    getChoice = () => readConsent(window.localStorage),
}: ObserveConsentEmbedsOptions = {}) {
    const contentRoot = documentRoot.querySelector("#main-content");

    if (!contentRoot) {
        return () => {};
    }

    const observer = createObserver(() => {
        const choice = getChoice();

        if (choice === "accepted") {
            loadConsentEmbeds(documentRoot);
            return;
        }

        revokeConsentEmbeds(documentRoot, { showSettings: choice === "rejected" });
    });

    observer.observe(contentRoot, { childList: true, subtree: true });

    return () => observer.disconnect();
}

type FocusAcceptedContentOptions = {
    browserWindow?: Pick<Window, "scrollTo">;
    documentRoot?: Document;
};

export function focusAcceptedContent({
    browserWindow = window,
    documentRoot = document,
}: FocusAcceptedContentOptions = {}) {
    documentRoot.querySelector<HTMLElement>("#main-content")?.focus({ preventScroll: true });
    browserWindow.scrollTo({ top: 0, left: 0, behavior: "auto" });
}

type RestoreConsentFocusOptions = {
    focusAccepted?: () => void;
    focusSettings?: () => void;
    openedFromSettings: boolean;
};

export function restoreConsentFocus(
    choice: TrackingChoice,
    {
        focusAccepted = focusAcceptedContent,
        focusSettings,
        openedFromSettings,
    }: RestoreConsentFocusOptions,
) {
    if (choice === "accepted") {
        focusAccepted();
        return;
    }

    if (openedFromSettings) {
        focusSettings?.();
    }
}

export function listenForConsentSettings(
    openSettings: () => void,
    documentRoot: Document = document,
) {
    const handleClick = (event: MouseEvent) => {
        const target = event.target as Element | null;

        if (typeof target?.closest !== "function") {
            return;
        }

        if (target.closest("[data-tracking-consent-settings]")) {
            openSettings();
        }
    };

    documentRoot.addEventListener("click", handleClick);

    return () => documentRoot.removeEventListener("click", handleClick);
}

function loadThirdPartyTrackers() {
    if (document.documentElement.dataset.environment !== "production") {
        return;
    }

    const load = () => {
        if (readConsent(window.localStorage) !== "accepted") {
            return;
        }

        void import("@/components/deferred-third-parties").then(({ initDeferredThirdParties }) => {
            if (readConsent(window.localStorage) === "accepted") {
                initDeferredThirdParties();
            }
        });
    };
    const schedule = () => {
        if ("requestIdleCallback" in window) {
            window.requestIdleCallback(load, { timeout: 3000 });
            return;
        }

        globalThis.setTimeout(load, 1);
    };

    if (document.readyState === "complete") {
        schedule();
        return;
    }

    window.addEventListener("load", schedule, { once: true });
}

export function clearTrackingCookies() {
    const cookieNames = document.cookie
        .split(";")
        .map((cookie) => cookie.split("=")[0].trim())
        .filter((name) => trackingCookiePrefixes.some((prefix) => name.startsWith(prefix)));
    const domainSuffixes = window.location.hostname
        .split(".")
        .map((_, index, labels) => labels.slice(index).join("."))
        .filter((domain) => domain.includes("."));

    cookieNames.forEach((name) => {
        document.cookie = `${name}=; Max-Age=0; Path=/; SameSite=Lax`;

        domainSuffixes.forEach((domain) => {
            document.cookie = `${name}=; Max-Age=0; Path=/; Domain=.${domain}; SameSite=Lax`;
        });
    });
}

type UseTrackingConsentOptions = {
    bannerRef: RefObject<HTMLElement | null>;
};

export function useTrackingConsent({ bannerRef }: UseTrackingConsentOptions) {
    const [state, dispatch] = useReducer(trackingConsentReducer, initialTrackingConsentState);
    const openedFromSettings = useRef(false);
    const thirdPartiesStarted = useRef(false);
    const animationFrame = useRef<number | null>(null);
    const focusTimer = useRef<number | null>(null);
    const hideTimer = useRef<number | null>(null);

    const settingsControls = useCallback(
        () => [...document.querySelectorAll<HTMLElement>("[data-tracking-consent-settings]")],
        [],
    );
    const startThirdParties = useCallback(() => {
        loadConsentEmbeds();

        if (thirdPartiesStarted.current) {
            return;
        }

        thirdPartiesStarted.current = true;
        loadThirdPartyTrackers();
        window.dispatchEvent(new CustomEvent("dlf:tracking-consent-accepted"));
    }, []);
    const revokeThirdParties = useCallback(() => {
        revokeConsentEmbeds();
        clearTrackingCookies();
        thirdPartiesStarted.current = false;
        window.dispatchEvent(new CustomEvent("dlf:tracking-consent-rejected"));

        void import("@/components/deferred-third-parties").then(({ revokeDeferredThirdParties }) =>
            revokeDeferredThirdParties(),
        );
    }, []);
    const clearPendingMotion = useCallback(() => {
        if (animationFrame.current !== null) {
            cancelAnimationFrame(animationFrame.current);
            animationFrame.current = null;
        }

        if (hideTimer.current !== null) {
            window.clearTimeout(hideTimer.current);
            hideTimer.current = null;
        }

        if (focusTimer.current !== null) {
            window.clearTimeout(focusTimer.current);
            focusTimer.current = null;
        }
    }, []);
    const openSettings = useCallback(() => {
        clearPendingMotion();
        openedFromSettings.current = true;
        dispatch({ type: "show" });
        animationFrame.current = requestAnimationFrame(() => {
            dispatch({ type: "finish-show" });
            bannerRef.current?.focus();
            animationFrame.current = null;
        });
    }, [bannerRef, clearPendingMotion]);
    const choose = useCallback(
        (choice: TrackingChoice) => {
            if (
                !applyTrackingChoice(choice, {
                    storage: window.localStorage,
                    startThirdParties,
                    revokeThirdParties,
                    scrollToTop: () => window.scrollTo({ top: 0, left: 0, behavior: "auto" }),
                })
            ) {
                return;
            }

            clearPendingMotion();
            dispatch({ type: "hide" });

            const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
            hideTimer.current = window.setTimeout(
                () => {
                    dispatch({ type: "finish-hide" });
                    hideTimer.current = null;

                    focusTimer.current = window.setTimeout(() => {
                        restoreConsentFocus(choice, {
                            focusSettings: () => settingsControls()[0]?.focus(),
                            openedFromSettings: openedFromSettings.current,
                        });
                        openedFromSettings.current = false;
                        focusTimer.current = null;
                    }, 50);
                },
                reducedMotion ? 0 : trackingConsentMotionDuration,
            );
        },
        [clearPendingMotion, revokeThirdParties, settingsControls, startThirdParties],
    );

    useEffect(() => {
        document.documentElement.dataset.dlfTrackingConsentHydrated = "true";

        const initialChoice = readConsent(window.localStorage);

        dispatch({ type: "initial-choice", choice: initialChoice });

        if (initialChoice !== "accepted") {
            clearTrackingCookies();
        }

        if (initialChoice === "rejected") {
            revokeConsentEmbeds();
        }

        if (initialChoice === "accepted") {
            startThirdParties();
        }
    }, [startThirdParties]);

    useEffect(() => listenForConsentSettings(openSettings), [openSettings]);

    useEffect(() => observeConsentEmbeds(), []);

    useEffect(() => clearPendingMotion, [clearPendingMotion]);

    return {
        accept: () => choose("accepted"),
        openSettings,
        reject: () => choose("rejected"),
        rendered: state.rendered,
        settingsHidden: !state.resolved || state.rendered,
        visualState: state.visualState,
    };
}
