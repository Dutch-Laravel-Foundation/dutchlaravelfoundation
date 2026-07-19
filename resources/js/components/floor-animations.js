import { gsap } from "gsap";

const animate = (selector, y) => {
    const elements = gsap.utils.toArray(selector);

    if (elements.length === 0) {
        return;
    }

    gsap.to(elements, {
        y,
        duration: 5,
        stagger: {
            each: 0.6,
            repeat: -1,
            yoyo: true,
        },
    });
};

animate(".top-floor-floating-element-bottom", 20);
animate(".top-floor-floating-element-top", -20);
