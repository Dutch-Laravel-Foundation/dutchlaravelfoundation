export const TRACKING_CONSENT_STORAGE_KEY = "dlf_tracking_consent";
export const TRACKING_CONSENT_VERSION = 1;

const validChoices = new Set(["accepted", "rejected"]);
const trackingCookiePrefixes = ["_ga", "_gid", "_gat", "_gcl_", "_li_", "li_", "leadinfo"];

const readConsent = (storage) => {
    try {
        const consent = JSON.parse(storage.getItem(TRACKING_CONSENT_STORAGE_KEY));

        if (consent?.version !== TRACKING_CONSENT_VERSION || !validChoices.has(consent?.choice)) {
            return null;
        }

        return consent.choice;
    } catch {
        return null;
    }
};

const storeConsent = (storage, choice) => {
    storage.setItem(
        TRACKING_CONSENT_STORAGE_KEY,
        JSON.stringify({
            version: TRACKING_CONSENT_VERSION,
            choice,
        }),
    );
};

const loadConsentEmbeds = (documentRoot) => {
    documentRoot.querySelectorAll("[data-consent-src]").forEach((embed) => {
        const source = embed.getAttribute("data-consent-src");

        if (!source) {
            return;
        }

        embed.setAttribute("src", source);
        embed.removeAttribute("data-consent-src");
        embed.hidden = false;
    });

    documentRoot.querySelectorAll("[data-consent-placeholder]").forEach((placeholder) => {
        placeholder.hidden = true;
    });

    documentRoot.querySelectorAll("[data-consent-fallback]").forEach((fallback) => {
        fallback.hidden = true;
    });
};

export function clearTrackingCookies({
    document: documentRoot = document,
    hostname = window.location.hostname,
} = {}) {
    const cookieNames = documentRoot.cookie
        .split(";")
        .map((cookie) => cookie.split("=")[0].trim())
        .filter((name) => trackingCookiePrefixes.some((prefix) => name.startsWith(prefix)));
    const domainSuffixes = hostname
        .split(".")
        .map((_, index, labels) => labels.slice(index).join("."))
        .filter((domain) => domain.includes("."));

    cookieNames.forEach((name) => {
        documentRoot.cookie = `${name}=; Max-Age=0; Path=/; SameSite=Lax`;

        domainSuffixes.forEach((domain) => {
            documentRoot.cookie = `${name}=; Max-Age=0; Path=/; Domain=.${domain}; SameSite=Lax`;
        });
    });
}

export function initTrackingConsent({
    document: documentRoot = document,
    storage = window.localStorage,
    loadTrackers = () => {},
    clearTrackerCookies = () =>
        clearTrackingCookies({
            document: documentRoot,
            hostname: documentRoot.location?.hostname ?? "",
        }),
    reload = () => window.location.reload(),
} = {}) {
    const banner = documentRoot.querySelector("[data-tracking-consent-banner]");
    const settings = documentRoot.querySelectorAll("[data-tracking-consent-settings]");
    const accept = documentRoot.querySelector("[data-tracking-consent-accept]");
    const reject = documentRoot.querySelector("[data-tracking-consent-reject]");

    if (!banner || settings.length === 0 || !accept || !reject) {
        return;
    }

    const initialChoice = readConsent(storage);
    let thirdPartiesLoaded = false;
    let openedFromSettings = false;

    if (initialChoice !== "accepted") {
        clearTrackerCookies();
    }

    const startThirdParties = () => {
        if (thirdPartiesLoaded) {
            return;
        }

        thirdPartiesLoaded = true;
        loadConsentEmbeds(documentRoot);
        loadTrackers();
    };

    const showBanner = () => {
        banner.hidden = false;
        settings.forEach((control) => {
            control.hidden = true;
        });
    };

    const hideBanner = () => {
        banner.hidden = true;
        settings.forEach((control) => {
            control.hidden = false;
        });
    };

    const choose = (choice) => {
        try {
            storeConsent(storage, choice);
        } catch {
            return;
        }

        hideBanner();

        if (choice === "accepted") {
            startThirdParties();

            if (openedFromSettings) {
                settings[0].focus();
            }

            return;
        }

        clearTrackerCookies();

        if (thirdPartiesLoaded) {
            reload();
            return;
        }

        if (openedFromSettings) {
            settings[0].focus();
        }
    };

    settings.forEach((control) => {
        control.addEventListener("click", () => {
            openedFromSettings = true;
            showBanner();
            banner.focus();
        });
    });
    accept.addEventListener("click", () => choose("accepted"));
    reject.addEventListener("click", () => choose("rejected"));

    if (initialChoice === null) {
        showBanner();
        return;
    }

    hideBanner();

    if (initialChoice === "accepted") {
        startThirdParties();
    }
}
