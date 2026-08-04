import { Button } from "@base-ui/react/button";

import { SmartLink } from "../ui/SmartLink";
import { SearchIcon } from "./icons";

type TopbarProps = {
    inert?: boolean;
    onPrepareSearch: () => void;
    onSearch: () => void;
    onNavigate?: () => void;
    mobile?: boolean;
};

export function Topbar({
    inert,
    mobile = false,
    onNavigate,
    onPrepareSearch,
    onSearch,
}: TopbarProps) {
    return (
        <div
            className={mobile ? "dlf-topbar dlf-mobile-menu-topbar" : "dlf-topbar"}
            inert={inert || undefined}
        >
            <div className="dlf-shell-container dlf-topbar-inner">
                <Button
                    className="dlf-topbar-search js-vragenai-trigger"
                    onPointerEnter={onPrepareSearch}
                    onFocus={onPrepareSearch}
                    onTouchStart={onPrepareSearch}
                    onClick={onSearch}
                >
                    <SearchIcon />
                    <span>Zoeken</span>
                </Button>
                <span className="dlf-recognized-by">
                    <span>Recognized by</span>
                    <img
                        src="/assets/img/laravel-wordmark-white.svg"
                        alt="Laravel"
                        width="1280"
                        height="314"
                    />
                </span>
                <span className="dlf-topbar-links">
                    <SmartLink href="/aanvraag" onClick={onNavigate}>
                        Match je project
                    </SmartLink>
                    <span className="dlf-topbar-separator" aria-hidden="true">
                        |
                    </span>
                    <SmartLink href="/contact" onClick={onNavigate}>
                        Contact
                    </SmartLink>
                </span>
            </div>
        </div>
    );
}
