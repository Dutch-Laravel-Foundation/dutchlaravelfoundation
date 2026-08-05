import { Button } from "@base-ui/react/button";
import { router } from "@inertiajs/react";
import { type KeyboardEvent, useCallback, useEffect, useRef, useState } from "react";

import { useHeaderBehavior } from "@/hooks/useHeaderBehavior";
import { useVragenAiSearch } from "@/hooks/useVragenAiSearch";
import { cn } from "@/lib/utils";

import { SmartLink } from "../ui/SmartLink";
import { DesktopNavigation } from "./DesktopNavigation";
import { CloseIcon, MenuIcon } from "./icons";
import { MobileNavigation } from "./MobileNavigation";
import { Topbar } from "./Topbar";
import { type NavigationItem } from "./types";

type HeaderProps = {
    navigation: readonly NavigationItem[];
    siteName: string;
};

export function Header({ navigation, siteName }: HeaderProps) {
    const [mobileOpen, setMobileOpen] = useState(false);
    const [mobileMenuMounted, setMobileMenuMounted] = useState(false);
    const headerRef = useRef<HTMLElement>(null);
    const menuButtonRef = useRef<HTMLButtonElement>(null);
    const closeButtonRef = useRef<HTMLButtonElement>(null);
    const closeTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const navigatedVisitId = useRef<string | null>(null);
    const vragenAi = useVragenAiSearch();

    useHeaderBehavior(headerRef);

    const closeMobileMenu = useCallback(() => {
        if (closeTimer.current) {
            clearTimeout(closeTimer.current);
        }

        setMobileOpen(false);
        closeTimer.current = setTimeout(() => setMobileMenuMounted(false), 200);
    }, []);

    useEffect(() => {
        document.documentElement.classList.toggle("dlf-menu-open", mobileOpen);
        const inertTargets = document.querySelectorAll<HTMLElement>(
            ".dlf-skip-link, #main-content, .dlf-footer, #vragenai-app",
        );

        inertTargets.forEach((element) => {
            element.inert = mobileOpen;
        });

        return () => {
            document.documentElement.classList.remove("dlf-menu-open");
            inertTargets.forEach((element) => {
                element.inert = false;
            });
        };
    }, [mobileOpen]);

    useEffect(() => {
        const closeOnEscape = (event: globalThis.KeyboardEvent) => {
            if (event.key !== "Escape" || !mobileOpen) {
                return;
            }

            closeMobileMenu();
            requestAnimationFrame(() => menuButtonRef.current?.focus());
        };

        window.addEventListener("keydown", closeOnEscape);

        return () => window.removeEventListener("keydown", closeOnEscape);
    }, [closeMobileMenu, mobileOpen]);

    useEffect(() => {
        if (!mobileMenuMounted) {
            return;
        }

        let cachedNavigationFrame: number | null = null;
        const stopListeningForNavigation = router.on("navigate", (event) => {
            navigatedVisitId.current = event.detail.visitId ?? null;

            if (event.detail.cached) {
                navigatedVisitId.current = null;
                cachedNavigationFrame = requestAnimationFrame(closeMobileMenu);
            }
        });
        const stopListeningForFinish = router.on("finish", (event) => {
            const { visit } = event.detail;

            if (visit.id !== navigatedVisitId.current) {
                return;
            }

            navigatedVisitId.current = null;

            if (visit.completed) {
                closeMobileMenu();
            }
        });

        return () => {
            if (cachedNavigationFrame !== null) {
                cancelAnimationFrame(cachedNavigationFrame);
            }

            stopListeningForNavigation();
            stopListeningForFinish();
        };
    }, [closeMobileMenu, mobileMenuMounted]);

    useEffect(
        () => () => {
            if (closeTimer.current) {
                clearTimeout(closeTimer.current);
            }
        },
        [],
    );

    const openMobileMenu = () => {
        if (closeTimer.current) {
            clearTimeout(closeTimer.current);
        }

        setMobileMenuMounted(true);
        requestAnimationFrame(() => {
            setMobileOpen(true);
            requestAnimationFrame(() => closeButtonRef.current?.focus());
        });
    };
    const trapMobileMenuFocus = (event: KeyboardEvent<HTMLDivElement>) => {
        if (event.key !== "Tab") {
            return;
        }

        const focusable = [
            ...event.currentTarget.querySelectorAll<HTMLElement>(
                "a[href], button:not([disabled]), summary",
            ),
        ].filter((element) => element.offsetParent !== null);
        const first = focusable[0];
        const last = focusable.at(-1);

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    };
    const openSearch = () => {
        void vragenAi.open();
    };
    const closeAndOpenSearch = () => {
        closeMobileMenu();
        void vragenAi.open();
    };

    return (
        <>
            <header
                ref={headerRef}
                className={cn("dlf-header", mobileOpen && "dlf-header--menu-open")}
            >
                <Topbar
                    inert={mobileOpen}
                    onPrepareSearch={() => void vragenAi.prepare()}
                    onSearch={openSearch}
                />

                <div
                    className="dlf-shell-container dlf-header-row"
                    inert={mobileOpen ? true : undefined}
                >
                    <SmartLink
                        className="dlf-header-logo"
                        href="/"
                        aria-label={`${siteName} — home`}
                    >
                        <img
                            src="/assets/img/logo-2025.svg"
                            alt={siteName}
                            width="562"
                            height="236"
                        />
                    </SmartLink>

                    <DesktopNavigation navigation={navigation} />

                    <Button
                        ref={menuButtonRef}
                        className="dlf-mobile-toggle"
                        type="button"
                        aria-controls="dlf-mobile-menu"
                        aria-expanded={mobileOpen}
                        aria-label="Menu openen"
                        onClick={openMobileMenu}
                    >
                        <MenuIcon />
                    </Button>
                </div>

                {mobileMenuMounted ? (
                    <div
                        id="dlf-mobile-menu"
                        className="dlf-mobile-menu"
                        role="dialog"
                        aria-modal="true"
                        aria-label="Menu"
                        onKeyDown={trapMobileMenuFocus}
                        style={{
                            opacity: mobileOpen ? 1 : 0,
                            transition: "opacity 200ms",
                        }}
                    >
                        <Topbar
                            mobile
                            onPrepareSearch={() => void vragenAi.prepare()}
                            onSearch={closeAndOpenSearch}
                        />

                        <div className="dlf-mobile-menu-header">
                            <SmartLink href="/" aria-label={`${siteName} — home`}>
                                <img
                                    src="/assets/img/logo-2025.svg"
                                    alt={siteName}
                                    width="562"
                                    height="236"
                                />
                            </SmartLink>
                            <Button
                                ref={closeButtonRef}
                                className="dlf-mobile-close"
                                type="button"
                                aria-label="Menu sluiten"
                                onClick={() => {
                                    closeMobileMenu();
                                    requestAnimationFrame(() => menuButtonRef.current?.focus());
                                }}
                            >
                                <CloseIcon />
                            </Button>
                        </div>

                        <MobileNavigation navigation={navigation} />
                    </div>
                ) : null}
            </header>
            <div id="vragenai-app" />
        </>
    );
}
