// Headroom.js
import Headroom from "headroom.js";

import hljs from "highlight.js/lib/core";
import bash from "highlight.js/lib/languages/bash";
import css from "highlight.js/lib/languages/css";
import graphql from "highlight.js/lib/languages/graphql";
import javascript from "highlight.js/lib/languages/javascript";
import json from "highlight.js/lib/languages/json";
import php from "highlight.js/lib/languages/php";
import shell from "highlight.js/lib/languages/shell";
import sql from "highlight.js/lib/languages/sql";
import xml from "highlight.js/lib/languages/xml";
import typescript from "highlight.js/lib/languages/typescript";

import "highlight.js/styles/github.css";

hljs.registerLanguage("bash", bash);
hljs.registerLanguage("css", css);
hljs.registerLanguage("graphql", graphql);
hljs.registerLanguage("javascript", javascript);
hljs.registerLanguage("json", json);
hljs.registerLanguage("php", php);
hljs.registerLanguage("shell", shell);
hljs.registerLanguage("sql", sql);
hljs.registerLanguage("xml", xml);
hljs.registerLanguage("typescript", typescript);

window.addEventListener("load", (event) => {
    document.querySelectorAll("pre code").forEach((el) => {
        hljs.highlightElement(el);
    });
});

var header = document.querySelector("header"),
    banner = document.querySelector(".banner"),
    nav = header.querySelector("nav.animated");

var includeBannerHeight = false;
var bannerHeight = combinedHeaderHeight(includeBannerHeight);

let isIE = /*@cc_on!@*/ false || !!document.documentMode;

if (header != null && isIE === false) {
    var headerHide = new Headroom(header, {
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

    // When the banner has changing heights else you can remove this part
    window.addEventListener(
        "load",
        function () {
            headerHide.offset = {
                down: combinedHeaderHeight(includeBannerHeight),
                up: combinedHeaderHeight(includeBannerHeight),
            };
        },
        false
    );
}

function combinedHeaderHeight(includeBanner = true) {
    return (
        header.offsetHeight +
        (includeBanner && banner ? banner.offsetHeight : 0)
    );
}

// SwiperJS
import "./components/swiper";

// AlpineJS
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

// AOS
import AOS from "aos";

let aosElement = document.querySelector("[data-aos]");

if (aosElement) {
    AOS.init({
        once: true,
    });

    window.addEventListener("load", AOS.refresh);
}

// GSAP
import { gsap } from "gsap";

gsap.to(".top-floor-floating-element-bottom", {
    y: 20,
    duration: 5,
    stagger: {
        each: 0.6,
        repeat: -1,
        yoyo: true,
    },
});

gsap.to(".top-floor-floating-element-top", {
    y: -20,
    duration: 5,
    stagger: {
        each: 0.6,
        repeat: -1,
        yoyo: true,
    },
});
