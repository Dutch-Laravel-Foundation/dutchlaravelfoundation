export const TRACKING_CONSENT_STORAGE_KEY = "dlf_tracking_consent";
export const TRACKING_CONSENT_VERSION = 1;

const validChoices = new Set(["accepted", "rejected"]);
const trackingCookiePrefixes = [
    "_ga",
    "_gid",
    "_gat",
    "_gcl_",
    "_li_",
    "li_",
    "leadinfo",
    "fathom",
];

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
    clearTrackerCookies = () => clearTrackingCookies(),
    reload = () => window.location.reload(),
} = {}) {
    const banner = documentRoot.querySelector("[data-tracking-consent-banner]");
    const settings = documentRoot.querySelector("[data-tracking-consent-settings]");
    const accept = documentRoot.querySelector("[data-tracking-consent-accept]");
    const reject = documentRoot.querySelector("[data-tracking-consent-reject]");

    if (!banner || !settings || !accept || !reject) {
        return;
    }

    const initialChoice = readConsent(storage);
    let trackersLoaded = false;
    let openedFromSettings = false;

    const startTrackers = () => {
        if (trackersLoaded) {
            return;
        }

        trackersLoaded = true;
        loadTrackers();
    };

    const showBanner = () => {
        banner.hidden = false;
        settings.hidden = true;
    };

    const hideBanner = () => {
        banner.hidden = true;
        settings.hidden = false;
    };

    const choose = (choice) => {
        try {
            storeConsent(storage, choice);
        } catch {
            return;
        }

        hideBanner();

        if (choice === "accepted") {
            startTrackers();

            if (openedFromSettings) {
                settings.focus();
            }

            return;
        }

        clearTrackerCookies();

        if (initialChoice === "accepted") {
            reload();
            return;
        }

        if (openedFromSettings) {
            settings.focus();
        }
    };

    settings.addEventListener("click", () => {
        openedFromSettings = true;
        showBanner();
        banner.focus();
    });
    accept.addEventListener("click", () => choose("accepted"));
    reject.addEventListener("click", () => choose("rejected"));

    if (initialChoice === null) {
        showBanner();
        return;
    }

    hideBanner();

    if (initialChoice === "accepted") {
        startTrackers();
    }
}
