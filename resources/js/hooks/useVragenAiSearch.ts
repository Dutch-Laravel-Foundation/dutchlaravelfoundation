import { useCallback, useRef } from "react";

export function useVragenAiSearch() {
    const ready = useRef(false);
    const loading = useRef<Promise<void> | null>(null);

    const prepare = useCallback(() => {
        loading.current ??= import("@/components/vragen-ai-search")
            .then(({ initVragenAiSearch }) => initVragenAiSearch())
            .then(() => {
                ready.current = true;
            });

        return loading.current;
    }, []);
    const open = useCallback(async () => {
        if (!ready.current) {
            await prepare();
        }

        window.dispatchEvent(new CustomEvent("open-vragen-ai"));
    }, [prepare]);

    return { open, prepare };
}
