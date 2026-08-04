const consentValues = (value) => ({
    ad_personalization: value,
    ad_storage: value,
    ad_user_data: value,
    analytics_storage: value,
});

const updateGoogleConsent = (browserWindow, value) => {
    browserWindow.dataLayer = browserWindow.dataLayer || [];
    const parameters = ["consent", "update", consentValues(value)];

    if (typeof browserWindow.gtag === "function") {
        browserWindow.gtag(...parameters);
        return;
    }

    browserWindow.dataLayer.push(parameters);
};

const loadScript = (documentRoot, source, attributes = {}) => {
    if (documentRoot.querySelector(`script[src="${source}"]`)) {
        return;
    }

    const script = documentRoot.createElement("script");
    script.src = source;
    script.async = true;
    script.setAttribute("data-dlf-tracker", "");

    Object.entries(attributes).forEach(([name, value]) => {
        script.setAttribute(name, value);
    });

    documentRoot.head.append(script);
};

const initGoogleTagManager = (documentRoot, browserWindow) => {
    browserWindow.dataLayer = browserWindow.dataLayer || [];
    browserWindow.dataLayer.push({
        "gtm.start": Date.now(),
        event: "gtm.js",
    });
    loadScript(documentRoot, "https://www.googletagmanager.com/gtm.js?id=GTM-N75FRC56");
};

const initLeadinfo = (documentRoot, browserWindow) => {
    const namespace = "leadinfo";

    if (browserWindow[namespace] && !browserWindow[namespace].dlfRevoked) {
        return;
    }

    browserWindow.GlobalLeadinfoNamespace = browserWindow.GlobalLeadinfoNamespace || [];
    browserWindow.GlobalLeadinfoNamespace.push(namespace);
    browserWindow[namespace] = (...parameters) => {
        browserWindow[namespace].q.push(parameters);
    };
    browserWindow[namespace].q = [];
    browserWindow[namespace].t = Date.now();
    loadScript(documentRoot, "https://cdn.leadinfo.net/ping.js");
};

const initLinkedIn = (documentRoot, browserWindow) => {
    browserWindow._linkedin_partner_id = "8379674";
    browserWindow._linkedin_data_partner_ids = browserWindow._linkedin_data_partner_ids || [];
    browserWindow._linkedin_data_partner_ids.push(browserWindow._linkedin_partner_id);

    if (!browserWindow.lintrk || browserWindow.lintrk.dlfRevoked) {
        browserWindow.lintrk = (...parameters) => {
            browserWindow.lintrk.q.push(parameters);
        };
    }

    browserWindow.lintrk.q = browserWindow.lintrk.q || [];
    loadScript(documentRoot, "https://snap.licdn.com/li.lms-analytics/insight.min.js");
};

export function initDeferredThirdParties({
    document: documentRoot = document,
    window: browserWindow = window,
} = {}) {
    updateGoogleConsent(browserWindow, "granted");
    initGoogleTagManager(documentRoot, browserWindow);
    initLeadinfo(documentRoot, browserWindow);
    initLinkedIn(documentRoot, browserWindow);
}

export function revokeDeferredThirdParties({
    document: documentRoot = document,
    window: browserWindow = window,
} = {}) {
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

    browserWindow.leadinfo = () => {};
    browserWindow.leadinfo.dlfRevoked = true;
    browserWindow.leadinfo.q = [];
    browserWindow.lintrk = () => {};
    browserWindow.lintrk.dlfRevoked = true;
    browserWindow.lintrk.q = [];
}
