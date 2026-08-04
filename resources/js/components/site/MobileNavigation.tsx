import { cn } from "@/lib/utils";
import { useState } from "react";

import { DlfButtonLink } from "../ui/DlfButton";
import { SmartLink } from "../ui/SmartLink";
import { ChevronIcon } from "./icons";
import { type NavigationItem } from "./types";

type MobileNavigationProps = {
    navigation: readonly NavigationItem[];
    onNavigate: () => void;
};

type MobileNavigationGroupProps = {
    item: NavigationItem;
    onNavigate: () => void;
};

function MobileNavigationGroup({ item, onNavigate }: MobileNavigationGroupProps) {
    const [open, setOpen] = useState(item.isAncestor);

    return (
        <details
            className="dlf-mobile-nav-group"
            open={open}
            onToggle={(event) => setOpen(event.currentTarget.open)}
        >
            <summary
                className={cn(
                    "dlf-mobile-nav-link",
                    (item.isCurrent || item.isAncestor) && "dlf-mobile-nav-link--active",
                )}
            >
                <span>{item.title}</span>
                <ChevronIcon className="dlf-mobile-nav-chevron" />
            </summary>
            <ul className="dlf-mobile-submenu">
                {item.children.map((child) => (
                    <li key={child.id}>
                        <SmartLink
                            className={cn(
                                "dlf-mobile-submenu-link",
                                child.isCurrent && "dlf-mobile-submenu-link--active",
                            )}
                            href={child.url ?? child.permalink ?? "#"}
                            aria-current={child.isCurrent ? "page" : undefined}
                            onClick={onNavigate}
                        >
                            {child.title}
                        </SmartLink>
                    </li>
                ))}
            </ul>
        </details>
    );
}

export function MobileNavigation({ navigation, onNavigate }: MobileNavigationProps) {
    return (
        <nav className="dlf-mobile-navigation" aria-label="Mobiele hoofdnavigatie">
            <ul className="dlf-mobile-nav-list">
                {navigation.map((item) => (
                    <li className="dlf-mobile-nav-item" key={item.id}>
                        {item.children.length ? (
                            <MobileNavigationGroup item={item} onNavigate={onNavigate} />
                        ) : (
                            <SmartLink
                                className={cn(
                                    "dlf-mobile-nav-link",
                                    (item.isCurrent || item.isAncestor) &&
                                        "dlf-mobile-nav-link--active",
                                )}
                                href={item.url ?? item.permalink ?? "#"}
                                aria-current={item.isCurrent ? "page" : undefined}
                                onClick={onNavigate}
                            >
                                {item.title}
                            </SmartLink>
                        )}
                    </li>
                ))}
            </ul>

            <div className="dlf-mobile-actions">
                <DlfButtonLink
                    className="dlf-mobile-action"
                    href="/aanvraag"
                    face="outline-red"
                    shadow="red"
                    onClick={onNavigate}
                >
                    Match je project
                </DlfButtonLink>
                <DlfButtonLink
                    className="dlf-mobile-action"
                    href="/lid-worden"
                    face="red"
                    shadow="red"
                    onClick={onNavigate}
                >
                    Lid worden
                </DlfButtonLink>
            </div>
        </nav>
    );
}
