import { Button } from "@base-ui/react/button";
import { router } from "@inertiajs/react";
import { type FocusEvent, type MouseEvent, useEffect, useRef, useState } from "react";

import { cn } from "@/lib/utils";

import { DlfButtonLink } from "../ui/DlfButton";
import { SmartLink } from "../ui/SmartLink";
import { ChevronIcon } from "./icons";
import { type NavigationItem } from "./types";

type NavigationDropdownProps = {
    item: NavigationItem;
};

function NavigationDropdown({ item }: NavigationDropdownProps) {
    const [open, setOpen] = useState(false);
    const triggerRef = useRef<HTMLButtonElement>(null);
    const submenuId = `dlf-submenu-${item.id}`;

    useEffect(() => {
        return router.on("before", (event) => {
            const { visit } = event.detail;

            if (!visit.prefetch) {
                setOpen(false);
            }
        });
    }, []);

    const closeOnMouseLeave = (event: MouseEvent<HTMLLIElement>) => {
        const activeElement = event.currentTarget.ownerDocument.activeElement;

        if (!event.currentTarget.contains(activeElement) || activeElement === triggerRef.current) {
            setOpen(false);
        }
    };
    const closeWhenFocusLeaves = (event: FocusEvent<HTMLLIElement>) => {
        if (!event.currentTarget.contains(event.relatedTarget)) {
            setOpen(false);
        }
    };

    return (
        <li
            className={cn("dlf-nav-item dlf-nav-item--parent", open && "dlf-nav-item--open")}
            data-enhanced
            onMouseEnter={() => setOpen(true)}
            onMouseLeave={closeOnMouseLeave}
            onBlur={closeWhenFocusLeaves}
            onKeyDown={(event) => {
                if (event.key !== "Escape") {
                    return;
                }

                event.stopPropagation();
                event.preventDefault();
                setOpen(false);
                triggerRef.current?.focus();
            }}
        >
            <Button
                ref={triggerRef}
                className={cn("dlf-nav-link", item.isAncestor && "dlf-nav-link--active")}
                aria-expanded={open}
                aria-controls={submenuId}
                onClick={() => setOpen((current) => !current)}
            >
                {item.title}
            </Button>
            <ChevronIcon className="dlf-nav-chevron" />
            <ul id={submenuId} className="dlf-submenu">
                {item.children.map((child) => (
                    <li key={child.id}>
                        <SmartLink
                            className={cn(
                                "dlf-submenu-link",
                                (child.isCurrent || child.isAncestor) && "dlf-submenu-link--active",
                            )}
                            href={child.url ?? child.permalink ?? "#"}
                            aria-current={child.isCurrent ? "page" : undefined}
                        >
                            {child.title}
                        </SmartLink>
                    </li>
                ))}
            </ul>
        </li>
    );
}

type DesktopNavigationProps = {
    navigation: readonly NavigationItem[];
};

export function DesktopNavigation({ navigation }: DesktopNavigationProps) {
    return (
        <nav className="dlf-desktop-navigation" aria-label="Hoofdnavigatie">
            <ul className="dlf-desktop-nav-list">
                {navigation.map((item) =>
                    item.children.length ? (
                        <NavigationDropdown item={item} key={item.id} />
                    ) : (
                        <li className="dlf-nav-item" key={item.id}>
                            <SmartLink
                                className={cn(
                                    "dlf-nav-link",
                                    (item.isCurrent || item.isAncestor) && "dlf-nav-link--active",
                                )}
                                href={item.url ?? item.permalink ?? "#"}
                                aria-current={item.isCurrent ? "page" : undefined}
                            >
                                {item.title}
                            </SmartLink>
                        </li>
                    ),
                )}

                <li>
                    <DlfButtonLink className="dlf-nav-cta" href="/lid-worden" face="outline-red">
                        Lid worden
                    </DlfButtonLink>
                </li>
            </ul>
        </nav>
    );
}
