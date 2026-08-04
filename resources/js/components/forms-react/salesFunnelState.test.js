import { describe, expect, it } from "bun:test";

import {
    persistSalesFunnelState,
    SALES_FUNNEL_STORAGE_KEY,
    shouldTrackSalesFunnelAbandonment,
    shouldTrackSalesFunnelSubmit,
} from "./salesFunnelState";

describe("sales funnel state", () => {
    it("ignores storage write failures", () => {
        const storage = {
            setItem() {
                throw new DOMException("Storage is unavailable", "SecurityError");
            },
        };

        expect(() => persistSalesFunnelState(storage, { step: 2 })).not.toThrow();
    });

    it("stores the wizard state under its shared key", () => {
        const writes = [];
        const storage = {
            setItem(key, value) {
                writes.push([key, value]);
            },
        };

        persistSalesFunnelState(storage, { step: 2 });

        expect(writes).toEqual([[SALES_FUNNEL_STORAGE_KEY, '{"step":2}']]);
    });

    it("tracks abandonment only for a real visit after the wizard started", () => {
        expect(
            shouldTrackSalesFunnelAbandonment({
                alreadyTracked: false,
                prefetch: false,
                step: 2,
                submitted: false,
            }),
        ).toBeTrue();
        expect(
            shouldTrackSalesFunnelAbandonment({
                alreadyTracked: false,
                prefetch: true,
                step: 2,
                submitted: false,
            }),
        ).toBeFalse();
        expect(
            shouldTrackSalesFunnelAbandonment({
                alreadyTracked: false,
                prefetch: false,
                step: 0,
                submitted: false,
            }),
        ).toBeFalse();
        expect(
            shouldTrackSalesFunnelAbandonment({
                alreadyTracked: true,
                prefetch: false,
                step: 2,
                submitted: false,
            }),
        ).toBeFalse();
        expect(
            shouldTrackSalesFunnelAbandonment({
                alreadyTracked: false,
                prefetch: false,
                step: 2,
                submitted: true,
            }),
        ).toBeFalse();
    });

    it("only tracks the first submit activation", () => {
        expect(shouldTrackSalesFunnelSubmit(false)).toBeTrue();
        expect(shouldTrackSalesFunnelSubmit(true)).toBeFalse();
    });
});
