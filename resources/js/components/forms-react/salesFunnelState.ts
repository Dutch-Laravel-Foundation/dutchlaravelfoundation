export const SALES_FUNNEL_STORAGE_KEY = "dlf_sales_funnel";

type SalesFunnelStorage = Pick<Storage, "setItem">;

type SalesFunnelAbandonmentState = {
    alreadyTracked: boolean;
    prefetch: boolean;
    step: number;
    submitted: boolean;
};

export function persistSalesFunnelState(storage: SalesFunnelStorage, value: unknown) {
    try {
        storage.setItem(SALES_FUNNEL_STORAGE_KEY, JSON.stringify(value));
    } catch {
        // Persistence is optional; the wizard must remain usable when storage is unavailable.
    }
}

export function shouldTrackSalesFunnelAbandonment({
    alreadyTracked,
    prefetch,
    step,
    submitted,
}: SalesFunnelAbandonmentState) {
    return !prefetch && !submitted && step > 0 && !alreadyTracked;
}

export function shouldTrackSalesFunnelSubmit(submitted: boolean) {
    return !submitted;
}
