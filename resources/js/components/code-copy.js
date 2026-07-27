export const copyCode = async (button, clipboard = globalThis.navigator?.clipboard) => {
    const code = button.closest("[data-code-copy]")?.querySelector("code");

    if (!code || typeof clipboard?.writeText !== "function") {
        return false;
    }

    await clipboard.writeText(code.textContent ?? "");

    return true;
};

export const initCodeCopy = (root = document) => {
    root.querySelectorAll("[data-code-copy] button").forEach((button) => {
        button.addEventListener("click", async () => {
            try {
                if (!(await copyCode(button))) {
                    return;
                }
            } catch {
                return;
            }

            const copyLabel = button.dataset.copyLabel;
            const copiedLabel = button.dataset.copiedLabel;

            button.dataset.copyState = "copied";
            button.setAttribute("aria-label", copiedLabel);
            button.setAttribute("title", copiedLabel);

            globalThis.setTimeout(() => {
                delete button.dataset.copyState;
                button.setAttribute("aria-label", copyLabel);
                button.setAttribute("title", copyLabel);
            }, 1600);
        });
    });
};
