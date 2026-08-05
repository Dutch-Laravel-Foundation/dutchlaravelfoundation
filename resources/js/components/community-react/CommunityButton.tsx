import { type ComponentPropsWithoutRef, type ReactNode } from "react";

import { cn } from "@/lib/utils";

import { SmartLink } from "../ui/SmartLink";

function Contents({ children }: { children: ReactNode }) {
    return (
        <>
            <span className="dlf-community-button__face">{children}</span>
            <span className="dlf-community-button__back" aria-hidden="true" />
        </>
    );
}

type CommunityButtonLinkProps = ComponentPropsWithoutRef<"a"> & {
    children: ReactNode;
    light?: boolean;
};

export function CommunityButtonLink({
    children,
    className,
    light,
    ...props
}: CommunityButtonLinkProps) {
    return (
        <SmartLink
            className={cn(
                "dlf-community-button",
                light && "dlf-community-button--light",
                className,
            )}
            {...props}
        >
            <Contents>{children}</Contents>
        </SmartLink>
    );
}
