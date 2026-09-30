import { type ComponentPropsWithoutRef, type ReactNode } from "react";

import { cn } from "@/lib/utils";

import { SmartLink } from "./SmartLink";

export type DlfButtonFace = "black" | "outline-red" | "outline-white" | "red" | "white";

export type DlfButtonShadow = "black" | "red" | "white";

type DlfButtonContentsProps = {
    children: ReactNode;
    face: DlfButtonFace;
    shadow?: DlfButtonShadow;
};

function DlfButtonContents({ children, face, shadow }: DlfButtonContentsProps) {
    return (
        <>
            <span className={`dlf-btn-face dlf-btn-face--${face}`}>{children}</span>
            <span
                className={cn("dlf-btn-shadow", shadow && `dlf-btn-shadow--${shadow}`)}
                aria-hidden="true"
            />
        </>
    );
}

type DlfButtonProps = ComponentPropsWithoutRef<"button"> & DlfButtonContentsProps;

export function DlfButton({ children, className, face, shadow, ...props }: DlfButtonProps) {
    return (
        <button className={cn("dlf-btn", className)} {...props}>
            <DlfButtonContents face={face} shadow={shadow}>
                {children}
            </DlfButtonContents>
        </button>
    );
}

type DlfButtonLinkProps = ComponentPropsWithoutRef<"a"> & DlfButtonContentsProps;

export function DlfButtonLink({ children, className, face, shadow, ...props }: DlfButtonLinkProps) {
    return (
        <SmartLink className={cn("dlf-btn", className)} {...props}>
            <DlfButtonContents face={face} shadow={shadow}>
                {children}
            </DlfButtonContents>
        </SmartLink>
    );
}
