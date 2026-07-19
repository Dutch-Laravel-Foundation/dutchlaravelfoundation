const header = document.querySelector(".dlf-header");

if (header) {
    const updateStickyOffset = () => {
        const headerHeight = header.classList.contains("slideUp")
            ? 0
            : header.offsetHeight;

        document.documentElement.style.setProperty(
            "--dlf-header-visible-height",
            `${headerHeight}px`
        );
        document.documentElement.style.setProperty(
            "--dlf-sticky-top",
            `${headerHeight + 24}px`
        );
    };
    const headerStateObserver = new MutationObserver(updateStickyOffset);
    const headerSizeObserver = new ResizeObserver(updateStickyOffset);

    headerStateObserver.observe(header, {
        attributeFilter: ["class"],
        attributes: true,
    });
    headerSizeObserver.observe(header);
    updateStickyOffset();
}
