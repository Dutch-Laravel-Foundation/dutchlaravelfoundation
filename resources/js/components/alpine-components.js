export const createHeaderMenu = ({ document: documentRoot = document } = {}) => ({
    mobileOpen: false,

    init() {
        this.syncPageState(this.mobileOpen);
        this.$watch("mobileOpen", (value) => this.syncPageState(value));
    },

    syncPageState(value) {
        documentRoot.documentElement.classList.toggle("dlf-menu-open", value);
        documentRoot
            .querySelectorAll(".dlf-skip-link, #main-content, .dlf-footer, #vragenai-app")
            .forEach((element) => {
                element.inert = value;
            });
    },

    openMobileMenu() {
        this.mobileOpen = true;
        this.$nextTick(() => this.$refs.closeButton.focus());
    },

    closeMobileMenu() {
        this.mobileOpen = false;
    },

    closeMobileMenuAndRestoreFocus() {
        if (!this.mobileOpen) {
            return;
        }

        this.mobileOpen = false;
        this.$nextTick(() => this.$refs.menuButton.focus());
    },

    closeMobileMenuAndOpenSearch() {
        this.mobileOpen = false;
        this.$dispatch("open-vragen-ai");
    },

    trapMobileMenuFocus(event) {
        const focusable = [
            ...event.currentTarget.querySelectorAll("a[href], button:not([disabled]), summary"),
        ].filter((element) => element.offsetParent !== null);

        const first = focusable[0];
        const last = focusable.at(-1);

        if (event.shiftKey && documentRoot.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (!event.shiftKey && documentRoot.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
});

export const createNavigationDropdown = () => ({
    open: false,

    openMenu() {
        this.open = true;
    },

    toggleMenu() {
        this.open = !this.open;
    },

    closeOnMouseLeave(event) {
        const activeElement = event.currentTarget.ownerDocument.activeElement;

        if (!event.currentTarget.contains(activeElement) || activeElement === this.$refs.trigger) {
            this.open = false;
        }
    },

    closeWhenFocusLeaves(event) {
        if (!event.currentTarget.contains(event.relatedTarget)) {
            this.open = false;
        }
    },

    closeAndRestoreFocus() {
        this.open = false;
        this.$refs.trigger.focus();
    },
});

const trapFocus = (event, documentRoot) => {
    const focusable = [
        ...event.currentTarget.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])',
        ),
    ].filter((element) => element.offsetParent !== null);

    const first = focusable[0];
    const last = focusable.at(-1);

    if (event.shiftKey && documentRoot.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && documentRoot.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

export const createMembersFilter = ({
    document: documentRoot = document,
    random = Math.random,
} = {}) => {
    const members = [...documentRoot.querySelectorAll("#members-data [data-member]")].map(
        (element) => ({ ...element.dataset }),
    );

    for (let index = members.length - 1; index > 0; index--) {
        const randomIndex = Math.floor(random() * (index + 1));

        [members[index], members[randomIndex]] = [members[randomIndex], members[index]];
    }

    const options = (key) =>
        [...new Set(members.map((member) => member[key]).filter(Boolean))].sort((left, right) =>
            left.localeCompare(right, "nl", { numeric: true }),
        );

    return {
        filterModalOpen: false,
        query: "",
        members,
        types: options("type"),
        employees: options("employees"),
        provinces: options("province"),
        filter: { types: [], employees: [], provinces: [] },

        syncBodyOverflow() {
            documentRoot.body.classList.toggle("overflow-hidden", this.filterModalOpen);
        },

        openFilterModal() {
            this.filterModalOpen = true;
            this.$nextTick(() => this.$refs.filterClose?.focus());
        },

        closeFilterModal() {
            if (!this.filterModalOpen) {
                return;
            }

            this.filterModalOpen = false;
            this.$nextTick(() => this.$refs.filterTrigger?.focus());
        },

        trapFilterFocus(event) {
            trapFocus(event, documentRoot);
        },

        toggleFilter(group, value) {
            const index = this.filter[group].indexOf(value);

            if (index === -1) {
                this.filter[group].push(value);
                return;
            }

            this.filter[group].splice(index, 1);
        },

        resetFilters() {
            this.filter = { types: [], employees: [], provinces: [] };
        },

        matches(group, value) {
            return this.filter[group].length === 0 || this.filter[group].includes(value);
        },

        location(member) {
            return [member.city, member.province].filter(Boolean).join(", ");
        },

        get activeFilterCount() {
            return (
                this.filter.types.length +
                this.filter.employees.length +
                this.filter.provinces.length
            );
        },

        get filteredMembers() {
            const query = this.query.trim().toLocaleLowerCase("nl");

            return this.members.filter((member) => {
                const matchesQuery =
                    query === "" || member.title.toLocaleLowerCase("nl").includes(query);

                return (
                    matchesQuery &&
                    this.matches("types", member.type) &&
                    this.matches("employees", member.employees) &&
                    this.matches("provinces", member.province)
                );
            });
        },

        get showMatchBanner() {
            return (
                this.query.trim() === "" &&
                this.activeFilterCount === 0 &&
                this.filteredMembers.length > 24
            );
        },

        get firstMembers() {
            return this.showMatchBanner ? this.filteredMembers.slice(0, 24) : this.filteredMembers;
        },

        get secondMembers() {
            return this.showMatchBanner ? this.filteredMembers.slice(24) : [];
        },

        get resultLabel() {
            const count = String(this.filteredMembers.length).padStart(2, "0");

            return `${count} ${this.filteredMembers.length === 1 ? "lid" : "leden"}`;
        },

        get filterResultLabel() {
            return `Toon ${this.filteredMembers.length} ${
                this.filteredMembers.length === 1 ? "lid" : "leden"
            }`;
        },
    };
};

export const createInternshipsFilter = ({
    document: documentRoot = document,
    random = Math.random,
} = {}) => {
    const internships = [
        ...documentRoot.querySelectorAll("#internships-data [data-internship]"),
    ].map((element) => ({ ...element.dataset }));

    for (let index = internships.length - 1; index > 0; index--) {
        const randomIndex = Math.floor(random() * (index + 1));

        [internships[index], internships[randomIndex]] = [
            internships[randomIndex],
            internships[index],
        ];
    }

    const provinces = [
        ...new Set(internships.map((internship) => internship.province).filter(Boolean)),
    ].sort((left, right) => left.localeCompare(right, "nl"));

    return {
        filterModalOpen: false,
        internships,
        provinces,
        hasSbb: internships.some((internship) => internship.sbb === "true"),
        filter: { provinces: [], sbb: [] },

        syncBodyOverflow() {
            documentRoot.body.classList.toggle("overflow-hidden", this.filterModalOpen);
        },

        openFilterModal() {
            this.filterModalOpen = true;
            this.$nextTick(() => this.$refs.filterClose?.focus());
        },

        closeFilterModal() {
            if (!this.filterModalOpen) {
                return;
            }

            this.filterModalOpen = false;
            this.$nextTick(() => this.$refs.filterTrigger?.focus());
        },

        trapFilterFocus(event) {
            trapFocus(event, documentRoot);
        },

        toggleFilter(group, value) {
            const index = this.filter[group].indexOf(value);

            if (index === -1) {
                this.filter[group].push(value);
                return;
            }

            this.filter[group].splice(index, 1);
        },

        resetFilters() {
            this.filter = { provinces: [], sbb: [] };
        },

        location(internship) {
            return [internship.city, internship.province].filter(Boolean).join(", ");
        },

        get activeFilterCount() {
            return this.filter.provinces.length + this.filter.sbb.length;
        },

        get filteredInternships() {
            return this.internships.filter((internship) => {
                const provinceMatches =
                    this.filter.provinces.length === 0 ||
                    this.filter.provinces.includes(internship.province);
                const sbbMatches = this.filter.sbb.length === 0 || internship.sbb === "true";

                return provinceMatches && sbbMatches;
            });
        },

        get resultLabel() {
            const count = String(this.filteredInternships.length).padStart(2, "0");

            return `${count}${this.filteredInternships.length === 1 ? " stage" : " stages"}`;
        },

        get filterResultLabel() {
            return `Toon ${this.resultLabel}`;
        },
    };
};

const SALES_FUNNEL_STORAGE_KEY = "dlf_sales_funnel";

export const createSalesFunnelWizard = () => ({
    step: 0,
    totalSteps: 6,
    submitted: false,
    analyticsEnabled: false,
    lastTrackedStepIndex: null,
    hasTrackedSubmit: false,
    hasTrackedAbandonment: false,
    lastCompletedStep: "0",
    stepLabels: ["Product", "Omschrijving", "Budget", "Partner", "Contact", "Overzicht"],
    errors: {},
    products: [
        { value: "applicatie", label: "Ontwikkelen van een applicatie/portal" },
        { value: "website", label: "Bouwen van een website" },
        {
            value: "overnemen",
            label: "Het overnemen/onderhouden van een bestaand systeem",
        },
        { value: "advies", label: "Advies/Kennis over Laravel" },
        {
            value: "audit",
            label: "Een beoordeling/audit op mijn bestaande applicatie",
        },
        { value: "vrijblijvend", label: "Vrijblijvend advies" },
    ],
    budgets: [
        { value: "0-10000", label: "€0 – €10.000" },
        { value: "10000-25000", label: "€10.000 – €25.000" },
        { value: "25000-50000", label: "€25.000 – €50.000" },
        { value: "50000-75000", label: "€50.000 – €75.000" },
        { value: "75000-150000", label: "€75.000 – €150.000" },
        { value: "150000+", label: "€150.000+" },
    ],
    companyTypes: [
        {
            value: "bureau",
            label: "Bureau / Agency",
            description: "Een team met meerdere specialisten",
        },
        {
            value: "zzp",
            label: "ZZP’er",
            description: "Een individuele specialist",
        },
        {
            value: "geen_voorkeur",
            label: "Geen voorkeur",
            description: "Beide opties zijn bespreekbaar",
        },
    ],
    data: {
        product: "",
        description: "",
        budget: "",
        company_type: "",
        name: "",
        company_name: "",
        email: "",
    },

    get productLabel() {
        return this.products.find((product) => product.value === this.data.product)?.label ?? "";
    },

    get budgetLabel() {
        return this.budgets.find((budget) => budget.value === this.data.budget)?.label ?? "";
    },

    get companyTypeLabel() {
        return this.companyTypes.find((type) => type.value === this.data.company_type)?.label ?? "";
    },

    save() {
        try {
            localStorage.setItem(
                SALES_FUNNEL_STORAGE_KEY,
                JSON.stringify({
                    step: this.step,
                    data: this.data,
                    lastCompletedStep: this.lastCompletedStep,
                    updatedAt: Date.now(),
                }),
            );
        } catch {}
    },

    restore() {
        try {
            const saved = JSON.parse(localStorage.getItem(SALES_FUNNEL_STORAGE_KEY));

            if (!saved) {
                return;
            }

            if (Date.now() - saved.updatedAt > 7 * 24 * 60 * 60 * 1000) {
                localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
                return;
            }

            this.step = saved.step;
            this.data = { ...this.data, ...saved.data };
            this.lastCompletedStep = saved.lastCompletedStep;
        } catch {
            localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
        }
    },

    clearStorage() {
        localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
    },

    reset() {
        this.step = 0;
        this.data = {
            product: "",
            description: "",
            budget: "",
            company_type: "",
            name: "",
            company_name: "",
            email: "",
        };
        this.lastCompletedStep = "0";
        this.errors = {};
        this.clearStorage();
        this.trackStepView(0);
    },

    init() {
        this.analyticsEnabled = this.$el.dataset.analyticsEnabled === "true";
        this.restore();
        this.trackStepView(this.step);
        this.$watch("step", () => this.save());
        this.$watch("data", () => this.save(), { deep: true });
    },

    validate() {
        this.errors = {};

        if (this.step === 0 && !this.data.product) {
            this.errors.product = "Selecteer een optie.";
        } else if (this.step === 1) {
            if (!this.data.description.trim()) {
                this.errors.description = "Vul een omschrijving in.";
            } else if (this.data.description.trim().length < 150) {
                this.errors.description = `Omschrijving moet minimaal 150 tekens bevatten (${this.data.description.trim().length}/150).`;
            }
        } else if (this.step === 2 && !this.data.budget) {
            this.errors.budget = "Selecteer een budgetrange.";
        } else if (this.step === 3 && !this.data.company_type) {
            this.errors.company_type = "Selecteer een voorkeur.";
        } else if (this.step === 4) {
            if (!this.data.name.trim()) this.errors.name = "Vul je naam in.";
            if (!this.data.company_name.trim())
                this.errors.company_name = "Vul je bedrijfsnaam in.";
            if (!this.data.email.trim()) {
                this.errors.email = "Vul je e-mailadres in.";
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.data.email)) {
                this.errors.email = "Vul een geldig e-mailadres in.";
            }
        }

        return Object.keys(this.errors).length === 0;
    },

    clearError(field) {
        delete this.errors[field];
    },

    selectProduct(product) {
        this.data.product = this.data.product === product ? "" : product;
        this.clearError("product");
    },

    selectBudget(budget) {
        this.data.budget = budget;
        this.clearError("budget");
    },

    selectCompanyType(companyType) {
        this.data.company_type = companyType;
        this.clearError("company_type");
    },

    nextStep() {
        if (!this.validate()) return;
        this.trackStepComplete(this.step);
        this.step += 1;
        this.lastCompletedStep = String(this.step);
        this.trackStepView(this.step);
    },

    prevStep() {
        if (this.step > 0) {
            this.step -= 1;
            this.trackStepView(this.step);
        }
    },

    goToStep(targetStep) {
        this.step = targetStep;
        this.trackStepView(this.step);
    },

    pushAnalyticsEvent(payload) {
        if (!this.analyticsEnabled) return;
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(payload);
    },

    trackStepView(stepIndex) {
        if (this.lastTrackedStepIndex === stepIndex) return;
        this.lastTrackedStepIndex = stepIndex;
        this.pushAnalyticsEvent({
            event: "sales_funnel_step_view",
            funnel_step: stepIndex + 1,
            funnel_step_name: this.stepLabels[stepIndex],
        });
    },

    trackStepComplete(stepIndex) {
        this.pushAnalyticsEvent({
            event: "sales_funnel_step_complete",
            funnel_step: stepIndex + 1,
            funnel_step_name: this.stepLabels[stepIndex],
        });
    },

    trackSubmit() {
        if (this.hasTrackedSubmit) return;
        this.submitted = true;
        this.hasTrackedSubmit = true;
        this.clearStorage();
        this.pushAnalyticsEvent({
            event: "sales_funnel_submit",
            funnel_product: this.productLabel,
            funnel_budget: this.budgetLabel,
            funnel_company_type: this.companyTypeLabel,
        });
    },

    trackAbandonment() {
        if (this.submitted || this.step === 0 || this.hasTrackedAbandonment) return;
        this.hasTrackedAbandonment = true;
        this.pushAnalyticsEvent({
            event: "sales_funnel_abandonment",
            funnel_step: this.step + 1,
            funnel_step_name: this.stepLabels[this.step],
        });
    },

    trackHiddenAbandonment() {
        if (document.visibilityState === "hidden") {
            this.trackAbandonment();
        }
    },
});
