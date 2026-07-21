const loadScript = (documentRoot, source, attributes = {}) => {
    if (documentRoot.querySelector(`script[src="${source}"]`)) {
        return;
    }

    const script = documentRoot.createElement("script");
    script.src = source;
    script.async = true;

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

    if (browserWindow[namespace]) {
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
    browserWindow.lintrk =
        browserWindow.lintrk ||
        ((...parameters) => {
            browserWindow.lintrk.q.push(parameters);
        });
    browserWindow.lintrk.q = browserWindow.lintrk.q || [];
    loadScript(documentRoot, "https://snap.licdn.com/li.lms-analytics/insight.min.js");
};

export function initDeferredThirdParties({
    document: documentRoot = document,
    window: browserWindow = window,
} = {}) {
    initGoogleTagManager(documentRoot, browserWindow);
    initLeadinfo(documentRoot, browserWindow);
    initLinkedIn(documentRoot, browserWindow);
    loadScript(documentRoot, "https://cdn.usefathom.com/script.js", {
        "data-site": "XTVPLNKC",
    });
}
