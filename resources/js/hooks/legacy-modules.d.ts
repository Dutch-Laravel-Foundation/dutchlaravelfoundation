declare module "@/components/deferred-third-parties" {
    export function initDeferredThirdParties(): void;
    export function revokeDeferredThirdParties(): void;
}

declare module "@/components/vragen-ai-search" {
    export function initVragenAiSearch(): Promise<void>;
}
