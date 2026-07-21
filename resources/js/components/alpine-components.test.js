import { describe, expect, it, mock } from "bun:test";

import {
    createHeaderMenu,
    createNavigationDropdown,
} from "./alpine-components";

describe("CSP-safe Alpine components", () => {
    it("opens and closes the mobile menu while maintaining inert page content", () => {
        const inertTargets = [{ inert: false }, { inert: false }];
        const documentRoot = {
            activeElement: null,
            documentElement: { classList: { toggle: mock(() => {}) } },
            querySelectorAll: () => inertTargets,
        };
        const menu = createHeaderMenu({ document: documentRoot });
        const closeButton = { focus: mock(() => {}) };
        const menuButton = { focus: mock(() => {}) };

        menu.$refs = { closeButton, menuButton };
        menu.$nextTick = (callback) => callback();
        menu.openMobileMenu();
        menu.syncPageState(true);

        expect(menu.mobileOpen).toBeTrue();
        expect(closeButton.focus).toHaveBeenCalledTimes(1);
        expect(inertTargets.every((target) => target.inert)).toBeTrue();

        menu.closeMobileMenuAndRestoreFocus();

        expect(menu.mobileOpen).toBeFalse();
        expect(menuButton.focus).toHaveBeenCalledTimes(1);
    });

    it("closes a desktop dropdown when focus leaves it", () => {
        const dropdown = createNavigationDropdown();
        dropdown.open = true;

        dropdown.closeWhenFocusLeaves({
            currentTarget: { contains: () => false },
            relatedTarget: {},
        });

        expect(dropdown.open).toBeFalse();
    });
});
