const loadScript = (source, attributes = {}) => {
    if (document.querySelector(`script[src="${source}"]`)) {
        return;
    }

    const script = document.createElement("script");
    script.src = source;
    script.async = true;

    Object.entries(attributes).forEach(([name, value]) => {
        script.setAttribute(name, value);
    });

    document.head.append(script);
};

const initGoogleTagManager = () => {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        "gtm.start": Date.now(),
        event: "gtm.js",
    });
    loadScript("https://www.googletagmanager.com/gtm.js?id=GTM-N75FRC56");
};

const initLeadinfo = () => {
    const namespace = "leadinfo";

    if (window[namespace]) {
        return;
    }

    window.GlobalLeadinfoNamespace = window.GlobalLeadinfoNamespace || [];
    window.GlobalLeadinfoNamespace.push(namespace);
    window[namespace] = (...parameters) => {
        window[namespace].q.push(parameters);
    };
    window[namespace].q = [];
    window[namespace].t = Date.now();
    loadScript("https://cdn.leadinfo.net/ping.js");
};

const initLinkedIn = () => {
    window._linkedin_partner_id = "8379674";
    window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
    window._linkedin_data_partner_ids.push(window._linkedin_partner_id);
    window.lintrk =
        window.lintrk ||
        ((...parameters) => {
            window.lintrk.q.push(parameters);
        });
    window.lintrk.q = window.lintrk.q || [];
    loadScript("https://snap.licdn.com/li.lms-analytics/insight.min.js");
};

export function initDeferredThirdParties() {
    initGoogleTagManager();
    initLeadinfo();
    initLinkedIn();
    loadScript("https://cdn.usefathom.com/script.js", {
        "data-site": "XTVPLNKC",
    });
}
