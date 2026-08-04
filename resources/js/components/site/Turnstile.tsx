import { useEffect, useRef } from "react";

let turnstileScript: Promise<void> | undefined;

type TurnstileApi = {
    remove: (widgetId: string) => void;
    render: (container: HTMLElement, options: { sitekey: string }) => string;
};

declare global {
    interface Window {
        turnstile?: TurnstileApi;
    }
}

function loadTurnstile() {
    if (window.turnstile) {
        return Promise.resolve();
    }

    turnstileScript ??= new Promise((resolve, reject) => {
        const existing = document.querySelector<HTMLScriptElement>(
            'script[src^="https://challenges.cloudflare.com/turnstile/v0/api.js"]',
        );

        if (existing) {
            existing.addEventListener("load", () => resolve(), { once: true });
            existing.addEventListener(
                "error",
                () => reject(new Error("Turnstile failed to load")),
                {
                    once: true,
                },
            );
            return;
        }

        const script = document.createElement("script");
        script.src = "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
        script.async = true;
        script.defer = true;
        script.addEventListener("load", () => resolve(), { once: true });
        script.addEventListener("error", () => reject(new Error("Turnstile failed to load")), {
            once: true,
        });
        document.head.append(script);
    });

    return turnstileScript;
}

export function Turnstile({ siteKey }: { siteKey?: string | null }) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!siteKey || !ref.current) {
            return;
        }

        const element = ref.current;
        let cancelled = false;
        let widgetId: string | undefined;
        const prepare = () => {
            void loadTurnstile()
                .then(() => {
                    if (!cancelled && !widgetId && window.turnstile) {
                        widgetId = window.turnstile.render(element, { sitekey: siteKey });
                    }
                })
                .catch(() => undefined);
        };
        const form = element.closest("form");
        form?.addEventListener("focusin", prepare, { once: true });
        form?.addEventListener("pointerdown", prepare, { once: true, passive: true });

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    prepare();
                }
            },
            { rootMargin: "600px" },
        );
        observer.observe(element);

        return () => {
            cancelled = true;
            observer.disconnect();
            form?.removeEventListener("focusin", prepare);
            form?.removeEventListener("pointerdown", prepare);
            if (widgetId && window.turnstile) {
                window.turnstile.remove(widgetId);
            }
        };
    }, [siteKey]);

    return <div ref={ref} className="cf-turnstile" data-sitekey={siteKey ?? undefined} />;
}
