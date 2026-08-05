type ConsentValue = "denied" | "granted";

type TrackerFunction = ((...parameters: unknown[]) => void) & {
    dlfRevoked?: boolean;
    q: unknown[][];
    t?: number;
};

type TrackerWindow = Window & {
    GlobalLeadinfoNamespace?: string[];
    _linkedin_data_partner_ids?: string[];
    _linkedin_partner_id?: string;
    dataLayer?: unknown[];
    gtag?: (...parameters: unknown[]) => void;
    leadinfo?: TrackerFunction;
    lintrk?: TrackerFunction;
};

type ThirdPartyOptions = {
    document?: Document;
    window?: TrackerWindow;
};

const consentValues = (value: ConsentValue) => ({
    ad_personalization: value,
    ad_storage: value,
    ad_user_data: value,
    analytics_storage: value,
});

function updateGoogleConsent(browserWindow: TrackerWindow, value: ConsentValue) {
    browserWindow.dataLayer ??= [];
    const parameters = ["consent", "update", consentValues(value)];

    if (typeof browserWindow.gtag === "function") {
        browserWindow.gtag(...parameters);
        return;
    }

    browserWindow.dataLayer.push(parameters);
}

function loadScript(documentRoot: Document, source: string, attributes: Record<string, string> = {}) {
    if (documentRoot.querySelector(`script[src="${source}"]`)) {
        return;
    }

    const script = documentRoot.createElement("script");
    script.src = source;
    script.async = true;
    script.setAttribute("data-dlf-tracker", "");

    Object.entries(attributes).forEach(([name, value]) => script.setAttribute(name, value));
    documentRoot.head.append(script);
}

function initGoogleTagManager(documentRoot: Document, browserWindow: TrackerWindow) {
    browserWindow.dataLayer ??= [];
    browserWindow.dataLayer.push({
        "gtm.start": Date.now(),
        event: "gtm.js",
    });
    loadScript(documentRoot, "https://www.googletagmanager.com/gtm.js?id=GTM-N75FRC56");
}

function initLeadinfo(documentRoot: Document, browserWindow: TrackerWindow) {
    if (browserWindow.leadinfo && !browserWindow.leadinfo.dlfRevoked) {
        return;
    }

    browserWindow.GlobalLeadinfoNamespace ??= [];
    browserWindow.GlobalLeadinfoNamespace.push("leadinfo");

    const leadinfo = ((...parameters: unknown[]) => {
        leadinfo.q.push(parameters);
    }) as TrackerFunction;
    leadinfo.q = [];
    leadinfo.t = Date.now();
    browserWindow.leadinfo = leadinfo;

    loadScript(documentRoot, "https://cdn.leadinfo.net/ping.js");
}

function initLinkedIn(documentRoot: Document, browserWindow: TrackerWindow) {
    browserWindow._linkedin_partner_id = "8379674";
    browserWindow._linkedin_data_partner_ids ??= [];
    browserWindow._linkedin_data_partner_ids.push(browserWindow._linkedin_partner_id);

    if (!browserWindow.lintrk || browserWindow.lintrk.dlfRevoked) {
        const lintrk = ((...parameters: unknown[]) => {
            lintrk.q.push(parameters);
        }) as TrackerFunction;
        lintrk.q = [];
        browserWindow.lintrk = lintrk;
    }

    loadScript(documentRoot, "https://snap.licdn.com/li.lms-analytics/insight.min.js");
}

export function initDeferredThirdParties({
    document: documentRoot = document,
    window: browserWindow = window,
}: ThirdPartyOptions = {}) {
    updateGoogleConsent(browserWindow, "granted");
    initGoogleTagManager(documentRoot, browserWindow);
    initLeadinfo(documentRoot, browserWindow);
    initLinkedIn(documentRoot, browserWindow);
}

export function revokeDeferredThirdParties({
    document: documentRoot = document,
    window: browserWindow = window,
}: ThirdPartyOptions = {}) {
    updateGoogleConsent(browserWindow, "denied");

    documentRoot
        .querySelectorAll(
            [
                "[data-dlf-tracker]",
                'script[src*="googletagmanager.com"]',
                'script[src*="leadinfo.net"]',
                'script[src*="licdn.com"]',
                'iframe[src*="leadinfo"]',
                'iframe[src*="linkedin"]',
                'img[src*="leadinfo"]',
                'img[src*="linkedin"]',
            ].join(","),
        )
        .forEach((element) => element.remove());

    browserWindow.GlobalLeadinfoNamespace = [];
    browserWindow._linkedin_data_partner_ids = [];

    const leadinfo = (() => {}) as TrackerFunction;
    leadinfo.dlfRevoked = true;
    leadinfo.q = [];
    browserWindow.leadinfo = leadinfo;

    const lintrk = (() => {}) as TrackerFunction;
    lintrk.dlfRevoked = true;
    lintrk.q = [];
    browserWindow.lintrk = lintrk;
}
