import Headroom from "headroom.js";
import Alpine from "@alpinejs/csp";

import "./components/header-aware-sticky";
import {
    createHeaderMenu,
    createInternshipsFilter,
    createMembersFilter,
    createNavigationDropdown,
    createSalesFunnelWizard,
} from "./components/alpine-components";
import { initProgressiveMedia } from "./components/progressive-media";
import { initFooterCtaStage } from "./components/footer-cta-stage";
import { initTrackingConsent } from "./components/tracking-consent";

const header = document.querySelector("header");
const banner = document.querySelector(".banner");

if (header && !document.documentMode) {
    const headerHide = new Headroom(header, {
        offset: 0,
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

    headerHide.init();

    window.addEventListener(
        "load",
        () => {
            const offset = header.offsetHeight + (banner ? banner.offsetHeight : 0);

            headerHide.offset = {
                down: offset,
                up: offset,
            };
        },
        { once: true },
    );
}

initProgressiveMedia();
initFooterCtaStage();

Alpine.data("headerMenu", createHeaderMenu);
Alpine.data("navigationDropdown", createNavigationDropdown);
Alpine.data("membersFilter", createMembersFilter);
Alpine.data("internshipsFilter", createInternshipsFilter);
Alpine.data("salesFunnelWizard", createSalesFunnelWizard);
window.Alpine = Alpine;
Alpine.start();

const loadForSelector = (selector, loader) => {
    if (document.querySelector(selector)) {
        void loader();
    }
};

loadForSelector("pre code", () => import("./components/syntax-highlighting"));
loadForSelector(
    ".js-swiper, .js-logos-swiper, .js-reviews-swiper",
    () => import("./components/swiper"),
);
loadForSelector("[data-client-logo-wall]", () => import("./components/client-logo-wall"));
loadForSelector("[data-editorial-article]", () => import("./components/editorial-article"));
loadForSelector("[data-lid-benefits-marquee]", () => import("./components/lid-benefits-marquee"));
loadForSelector("[data-aos]", () => import("./components/scroll-animations"));
loadForSelector(
    ".top-floor-floating-element-bottom, .top-floor-floating-element-top",
    () => import("./components/floor-animations"),
);

const vragenAiTriggers = [...document.querySelectorAll(".js-vragenai-trigger")];
let vragenAiReady = false;
let vragenAiLoading;

const prepareVragenAi = () => {
    vragenAiLoading ??= import("./components/vragen-ai-search")
        .then(({ initVragenAiSearch }) => initVragenAiSearch())
        .then(() => {
            vragenAiReady = true;
        });

    return vragenAiLoading;
};

vragenAiTriggers.forEach((trigger) => {
    ["pointerenter", "focusin", "touchstart"].forEach((eventName) => {
        trigger.addEventListener(eventName, prepareVragenAi, {
            once: true,
            passive: true,
        });
    });

    trigger.addEventListener(
        "click",
        async (event) => {
            if (vragenAiReady) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            await prepareVragenAi();
            trigger.click();
        },
        true,
    );
});

const turnstileWidget = document.querySelector(".cf-turnstile[data-sitekey]");

if (turnstileWidget) {
    let loadTurnstile;
    const prepareTurnstile = () => {
        loadTurnstile ??= import("./components/turnstile");

        return loadTurnstile;
    };
    const form = turnstileWidget.closest("form");

    form?.addEventListener("focusin", prepareTurnstile, { once: true });
    form?.addEventListener("pointerdown", prepareTurnstile, {
        once: true,
        passive: true,
    });

    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                if (!entries.some((entry) => entry.isIntersecting)) {
                    return;
                }

                observer.disconnect();
                void prepareTurnstile();
            },
            { rootMargin: "600px" },
        );

        observer.observe(turnstileWidget);
    }
}

const runWhenIdle = (callback) => {
    const schedule = () => {
        if ("requestIdleCallback" in window) {
            window.requestIdleCallback(callback, { timeout: 3000 });
            return;
        }

        window.setTimeout(callback, 1);
    };

    if (document.readyState === "complete") {
        schedule();
        return;
    }

    window.addEventListener("load", schedule, { once: true });
};

initTrackingConsent({
    loadTrackers: () => {
        if (document.documentElement.dataset.environment !== "production") {
            return;
        }

        runWhenIdle(() => {
            void import("./components/deferred-third-parties").then(
                ({ initDeferredThirdParties }) => initDeferredThirdParties(),
            );
        });
    },
});
