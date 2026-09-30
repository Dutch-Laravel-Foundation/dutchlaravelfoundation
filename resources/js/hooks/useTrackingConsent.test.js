import { describe, expect, it, mock } from "bun:test";

import {
    TRACKING_CONSENT_STORAGE_KEY,
    TRACKING_CONSENT_VERSION,
    applyTrackingChoice,
    trackingConsentReducer,
} from "./useTrackingConsent";
import * as trackingConsent from "./useTrackingConsent";

class FakeElement {
    attributes = new Map();
    hidden = true;
    setAttributeCalls = [];

    constructor(attributes = {}) {
        Object.entries(attributes).forEach(([name, value]) => {
            this.attributes.set(name, value);
        });
    }

    getAttribute(name) {
        return this.attributes.get(name) ?? null;
    }

    removeAttribute(name) {
        this.attributes.delete(name);
    }

    setAttribute(name, value) {
        this.setAttributeCalls.push([name, value]);
        this.attributes.set(name, value);
    }
}

describe("React tracking consent", () => {
    it("renders an undecided banner immediately without an entrance phase", () => {
        expect(
            trackingConsentReducer(undefined, {
                type: "initial-choice",
                choice: null,
            }),
        ).toEqual({
            rendered: true,
            resolved: true,
            visualState: "open",
        });
    });

    it("animates a reopened banner from closed to open", () => {
        const hidden = trackingConsentReducer(undefined, {
            type: "initial-choice",
            choice: "rejected",
        });
        const entering = trackingConsentReducer(hidden, { type: "show" });

        expect(entering).toEqual({
            rendered: true,
            resolved: true,
            visualState: "closed",
        });
        expect(trackingConsentReducer(entering, { type: "finish-show" })).toEqual({
            rendered: true,
            resolved: true,
            visualState: "open",
        });
    });

    it("keeps a closing banner rendered until its fade finishes", () => {
        const closing = trackingConsentReducer(undefined, { type: "hide" });

        expect(closing).toEqual({
            rendered: true,
            resolved: false,
            visualState: "closed",
        });
        expect(trackingConsentReducer(closing, { type: "finish-hide" })).toEqual({
            rendered: false,
            resolved: false,
            visualState: "closed",
        });
    });

    it("persists rejection before revoking trackers and never asks for a reload", () => {
        const events = [];
        const storage = {
            setItem: mock((key, value) => events.push(["stored", key, value])),
        };
        const revokeThirdParties = mock(() => events.push(["revoked"]));

        expect(
            applyTrackingChoice("rejected", {
                storage,
                startThirdParties: () => events.push(["started"]),
                revokeThirdParties,
            }),
        ).toBeTrue();

        expect(events).toEqual([
            [
                "stored",
                TRACKING_CONSENT_STORAGE_KEY,
                JSON.stringify({
                    version: TRACKING_CONSENT_VERSION,
                    choice: "rejected",
                }),
            ],
            ["revoked"],
        ]);
    });

    it("scrolls to the top after accepted consent activates third parties", () => {
        const events = [];

        applyTrackingChoice("accepted", {
            storage: { setItem: () => events.push("stored") },
            startThirdParties: () => events.push("started"),
            revokeThirdParties: () => events.push("revoked"),
            scrollToTop: () => events.push("scrolled"),
        });

        expect(events).toEqual(["stored", "started", "scrolled"]);
    });

    it("can activate and revoke consent-gated embeds without losing their source", () => {
        expect(typeof trackingConsent.loadConsentEmbeds).toBe("function");
        expect(typeof trackingConsent.revokeConsentEmbeds).toBe("function");

        const embed = new FakeElement({
            "data-consent-src": "https://www.youtube-nocookie.com/embed/example",
        });
        const placeholder = new FakeElement();
        placeholder.hidden = false;
        const settingsControl = new FakeElement();
        const document = {
            querySelectorAll(selector) {
                if (selector === "[data-consent-src]") {
                    return [embed];
                }

                if (selector === "[data-consent-placeholder], [data-consent-fallback]") {
                    return [placeholder];
                }

                if (selector === "[data-tracking-consent-embed-settings]") {
                    return [settingsControl];
                }

                return [];
            },
        };

        trackingConsent.loadConsentEmbeds(document);
        trackingConsent.loadConsentEmbeds(document);

        expect(embed.getAttribute("src")).toBe("https://www.youtube-nocookie.com/embed/example");
        expect(embed.getAttribute("data-consent-src")).toBe(
            "https://www.youtube-nocookie.com/embed/example",
        );
        expect(embed.hidden).toBeFalse();
        expect(embed.setAttributeCalls).toEqual([
            ["src", "https://www.youtube-nocookie.com/embed/example"],
        ]);
        expect(placeholder.hidden).toBeTrue();
        expect(settingsControl.hidden).toBeTrue();

        trackingConsent.revokeConsentEmbeds(document);

        expect(embed.getAttribute("src")).toBeNull();
        expect(embed.getAttribute("data-consent-src")).toBe(
            "https://www.youtube-nocookie.com/embed/example",
        );
        expect(embed.hidden).toBeTrue();
        expect(placeholder.hidden).toBeFalse();
        expect(settingsControl.hidden).toBeFalse();
    });

    it("activates accepted embeds when Inertia mounts them after the initial choice", () => {
        expect(typeof trackingConsent.observeConsentEmbeds).toBe("function");

        const embed = new FakeElement({
            "data-consent-src": "https://www.youtube-nocookie.com/embed/later",
        });
        const contentRoot = {};
        let handleMutation = () => {};
        const observer = {
            disconnect: mock(() => {}),
            observe: mock(() => {}),
        };
        const document = {
            querySelector: (selector) => (selector === "#main-content" ? contentRoot : null),
            querySelectorAll: (selector) => (selector === "[data-consent-src]" ? [embed] : []),
        };

        const stop = trackingConsent.observeConsentEmbeds({
            createObserver(callback) {
                handleMutation = callback;

                return observer;
            },
            documentRoot: document,
            getChoice: () => "accepted",
        });

        handleMutation();

        expect(observer.observe).toHaveBeenCalledWith(contentRoot, {
            childList: true,
            subtree: true,
        });
        expect(embed.getAttribute("src")).toBe("https://www.youtube-nocookie.com/embed/later");
        expect(embed.hidden).toBeFalse();

        stop();

        expect(observer.disconnect).toHaveBeenCalledTimes(1);
    });

    it("keeps later Inertia-mounted embeds blocked when consent is rejected", () => {
        const embed = new FakeElement({
            "data-consent-src": "https://www.youtube-nocookie.com/embed/rejected",
            src: "https://www.youtube-nocookie.com/embed/rejected",
        });
        embed.hidden = false;
        const placeholder = new FakeElement();
        const settingsControl = new FakeElement();
        let handleMutation = () => {};

        trackingConsent.observeConsentEmbeds({
            createObserver(callback) {
                handleMutation = callback;

                return { disconnect() {}, observe() {} };
            },
            documentRoot: {
                querySelector: () => ({}),
                querySelectorAll(selector) {
                    if (selector === "[data-consent-src]") {
                        return [embed];
                    }

                    if (selector === "[data-consent-placeholder], [data-consent-fallback]") {
                        return [placeholder];
                    }

                    if (selector === "[data-tracking-consent-embed-settings]") {
                        return [settingsControl];
                    }

                    return [];
                },
            },
            getChoice: () => "rejected",
        });

        handleMutation();

        expect(embed.getAttribute("src")).toBeNull();
        expect(embed.hidden).toBeTrue();
        expect(placeholder.hidden).toBeFalse();
        expect(settingsControl.hidden).toBeFalse();
    });

    it("revokes inserted embeds without exposing settings while consent is undecided", () => {
        const embed = new FakeElement({
            "data-consent-src": "https://www.youtube-nocookie.com/embed/undecided",
            src: "https://www.youtube-nocookie.com/embed/undecided",
        });
        embed.hidden = false;
        const placeholder = new FakeElement();
        const settingsControl = new FakeElement();
        let handleMutation = () => {};

        trackingConsent.observeConsentEmbeds({
            createObserver(callback) {
                handleMutation = callback;

                return { disconnect() {}, observe() {} };
            },
            documentRoot: {
                querySelector: () => ({}),
                querySelectorAll(selector) {
                    if (selector === "[data-consent-src]") {
                        return [embed];
                    }

                    if (selector === "[data-consent-placeholder], [data-consent-fallback]") {
                        return [placeholder];
                    }

                    if (selector === "[data-tracking-consent-embed-settings]") {
                        return [settingsControl];
                    }

                    return [];
                },
            },
            getChoice: () => null,
        });

        handleMutation();

        expect(embed.getAttribute("src")).toBeNull();
        expect(embed.hidden).toBeTrue();
        expect(placeholder.hidden).toBeFalse();
        expect(settingsControl.hidden).toBeTrue();
    });

    it("delegates settings clicks to controls mounted by later Inertia visits", () => {
        expect(typeof trackingConsent.listenForConsentSettings).toBe("function");

        let handleClick = () => {};
        const document = {
            addEventListener: mock((event, listener) => {
                if (event === "click") {
                    handleClick = listener;
                }
            }),
            removeEventListener: mock(() => {}),
        };
        const openSettings = mock(() => {});
        const stop = trackingConsent.listenForConsentSettings(openSettings, document);
        const dynamicControl = {
            closest: (selector) =>
                selector === "[data-tracking-consent-settings]" ? dynamicControl : null,
        };

        handleClick({ target: dynamicControl });

        expect(openSettings).toHaveBeenCalledTimes(1);

        stop();

        expect(document.removeEventListener).toHaveBeenCalledWith("click", handleClick);
    });

    it("keeps focus and scroll at the page top after an accepted banner closes", () => {
        expect(typeof trackingConsent.focusAcceptedContent).toBe("function");

        const content = { focus: mock(() => {}) };
        const browserWindow = { scrollTo: mock(() => {}) };

        trackingConsent.focusAcceptedContent({
            browserWindow,
            documentRoot: {
                querySelector: (selector) => (selector === "#main-content" ? content : null),
            },
        });

        expect(content.focus).toHaveBeenCalledWith({ preventScroll: true });
        expect(browserWindow.scrollTo).toHaveBeenCalledWith({
            behavior: "auto",
            left: 0,
            top: 0,
        });
    });

    it("restores main-content focus after first-time acceptance as well as reopened acceptance", () => {
        expect(typeof trackingConsent.restoreConsentFocus).toBe("function");

        const focusAccepted = mock(() => {});
        const focusSettings = mock(() => {});

        trackingConsent.restoreConsentFocus("accepted", {
            focusAccepted,
            focusSettings,
            openedFromSettings: false,
        });

        expect(focusAccepted).toHaveBeenCalledTimes(1);
        expect(focusSettings).not.toHaveBeenCalled();
    });
});
