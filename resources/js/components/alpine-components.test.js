import { describe, expect, it, mock } from "bun:test";

import {
    createHeaderMenu,
    createNavigationDropdown,
    createSalesFunnelWizard,
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

    it("selects sales funnel options through CSP-safe component methods", () => {
        const wizard = createSalesFunnelWizard();
        wizard.errors = {
            product: "Selecteer een optie.",
            budget: "Selecteer een budgetrange.",
            company_type: "Selecteer een voorkeur.",
        };

        wizard.selectProduct("applicatie");
        wizard.selectBudget("25000-50000");
        wizard.selectCompanyType("bureau");

        expect(wizard.data.product).toBe("applicatie");
        expect(wizard.data.budget).toBe("25000-50000");
        expect(wizard.data.company_type).toBe("bureau");
        expect(wizard.errors).toEqual({});

        wizard.selectProduct("applicatie");

        expect(wizard.data.product).toBe("");
    });
});
