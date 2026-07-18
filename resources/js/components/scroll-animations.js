import AOS from "aos";
import "aos/dist/aos.css";

AOS.init({
    once: true,
});

window.addEventListener("load", AOS.refresh, { once: true });
