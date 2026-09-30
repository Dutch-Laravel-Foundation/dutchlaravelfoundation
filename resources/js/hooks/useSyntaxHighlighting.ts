import { type RefObject, useEffect } from "react";

export function useSyntaxHighlighting(
    rootRef: RefObject<HTMLElement | null>,
    contentKey: unknown,
) {
    useEffect(() => {
        const root = rootRef.current;

        if (!root?.querySelector("pre code")) {
            return;
        }

        let active = true;

        void import("@/components/syntax-highlighting").then(({ highlightCodeBlocks }) => {
            if (active) {
                highlightCodeBlocks(root);
            }
        });

        return () => {
            active = false;
        };
    }, [contentKey, rootRef]);
}
