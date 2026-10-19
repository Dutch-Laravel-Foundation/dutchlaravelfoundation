import { useEffect } from "react";

export function useSiteDocument(pageSlug: string) {
    useEffect(() => {
        const openSearch = () => document.body.classList.add("overflow-hidden", "h-screen");
        const closeSearch = () => document.body.classList.remove("overflow-hidden", "h-screen");

        window.addEventListener("open-vragen-ai", openSearch);
        window.addEventListener("close-vragen-ai", closeSearch);

        return () => {
            closeSearch();
            window.removeEventListener("open-vragen-ai", openSearch);
            window.removeEventListener("close-vragen-ai", closeSearch);
        };
    }, [pageSlug]);
}
