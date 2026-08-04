declare module "headroom.js" {
    type HeadroomOptions = {
        offset?: number | { down: number; up: number };
        tolerance?: number | { down: number; up: number };
        classes?: {
            initial?: string;
            pinned?: string;
            unpinned?: string;
        };
    };

    export default class Headroom {
        constructor(element: HTMLElement, options?: HeadroomOptions);

        init(): void;
        destroy(): void;
        pin(): void;
        unpin(): void;
    }
}
