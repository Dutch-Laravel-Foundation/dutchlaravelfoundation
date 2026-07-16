const clientLogoWalls = document.querySelectorAll("[data-client-logo-wall]");

clientLogoWalls.forEach((wall) => {
    const cells = [...wall.querySelectorAll("[data-client-cell]")];
    let tick = 0;

    const updateLogos = () => {
        cells.forEach((cell, cellIndex) => {
            const logos = [...cell.querySelectorAll("[data-client-logo]")];

            if (logos.length === 0) {
                return;
            }

            const activeLogoIndex = Math.floor((tick + cellIndex) / 6) % logos.length;

            logos.forEach((logo, logoIndex) => {
                const isActive = logoIndex === activeLogoIndex;

                logo.classList.toggle("dlf-home-clients__logo--active", isActive);

                if (isActive) {
                    logo.removeAttribute("aria-hidden");
                    return;
                }

                logo.setAttribute("aria-hidden", "true");
            });
        });
    };

    updateLogos();

    window.setInterval(() => {
        tick += 1;
        updateLogos();
    }, 2500);
});
