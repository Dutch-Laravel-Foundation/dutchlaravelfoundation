import { Button } from "@base-ui/react/button";
import { Input } from "@base-ui/react/input";
import { router } from "@inertiajs/react";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";

import { Turnstile } from "@/components/site/Turnstile";
import { DlfButton } from "@/components/ui/DlfButton";

import {
    persistSalesFunnelState,
    SALES_FUNNEL_STORAGE_KEY,
    shouldTrackSalesFunnelAbandonment,
    shouldTrackSalesFunnelSubmit,
} from "./salesFunnelState";
import { type FormDefinitionData, type FormSubmissionStateData } from "./types";

const stepLabels = ["Product", "Omschrijving", "Budget", "Partner", "Contact", "Overzicht"];
const products = [
    ["applicatie", "Ontwikkelen van een applicatie/portal"],
    ["website", "Bouwen van een website"],
    ["overnemen", "Het overnemen/onderhouden van een bestaand systeem"],
    ["advies", "Advies/Kennis over Laravel"],
    ["audit", "Een beoordeling/audit op mijn bestaande applicatie"],
    ["vrijblijvend", "Vrijblijvend advies"],
] as const;
const budgets = [
    ["0-10000", "€0 – €10.000"],
    ["10000-25000", "€10.000 – €25.000"],
    ["25000-50000", "€25.000 – €50.000"],
    ["50000-75000", "€50.000 – €75.000"],
    ["75000-150000", "€75.000 – €150.000"],
    ["150000+", "€150.000+"],
] as const;
const companyTypes = [
    ["bureau", "Bureau / Agency", "Een team met meerdere specialisten"],
    ["zzp", "ZZP’er", "Een individuele specialist"],
    ["geen_voorkeur", "Geen voorkeur", "Beide opties zijn bespreekbaar"],
] as const;

type FunnelData = {
    budget: string;
    company_name: string;
    company_type: string;
    description: string;
    email: string;
    name: string;
    product: string;
};

const emptyData: FunnelData = {
    budget: "",
    company_name: "",
    company_type: "",
    description: "",
    email: "",
    name: "",
    product: "",
};

function stringOld(submission: FormSubmissionStateData, key: keyof FunnelData): string {
    const value = submission.old[key];
    return typeof value === "string" ? value : "";
}

function event(payload: Record<string, string | number>) {
    if (!import.meta.env.PROD) {
        return;
    }

    const target = window as typeof window & { dataLayer?: Array<Record<string, string | number>> };
    target.dataLayer ??= [];
    target.dataLayer.push(payload);
}

type SalesFunnelWizardProps = {
    captchaSiteKey?: string | null;
    csrfToken: string;
    form: FormDefinitionData;
    submission: FormSubmissionStateData;
};

export function SalesFunnelWizard({
    captchaSiteKey,
    csrfToken,
    form,
    submission,
}: SalesFunnelWizardProps) {
    const [step, setStep] = useState(() => {
        const value = Number(submission.old.last_completed_step ?? 0);
        return Number.isInteger(value) && value >= 0 && value < stepLabels.length ? value : 0;
    });
    const [lastCompletedStep, setLastCompletedStep] = useState(() => {
        const value = Number(submission.old.last_completed_step ?? 0);
        return Number.isInteger(value) && value >= 0 && value < stepLabels.length
            ? String(value)
            : "0";
    });
    const [data, setData] = useState<FunnelData>(() => ({
        budget: stringOld(submission, "budget"),
        company_name: stringOld(submission, "company_name"),
        company_type: stringOld(submission, "company_type"),
        description: stringOld(submission, "description"),
        email: stringOld(submission, "email"),
        name: stringOld(submission, "name"),
        product: stringOld(submission, "product"),
    }));
    const [errors, setErrors] = useState<Record<string, string>>(submission.errors);
    const [restored, setRestored] = useState(false);
    const submitted = useRef(false);
    const abandonmentTracked = useRef(false);
    const clearPersistence = useRef(false);
    const skipInitialPersistence = useRef(true);

    const productLabel = useMemo(
        () => products.find(([value]) => value === data.product)?.[1] ?? "",
        [data.product],
    );
    const budgetLabel = useMemo(
        () => budgets.find(([value]) => value === data.budget)?.[1] ?? "",
        [data.budget],
    );
    const companyTypeLabel = useMemo(
        () => companyTypes.find(([value]) => value === data.company_type)?.[1] ?? "",
        [data.company_type],
    );

    const trackView = useCallback((index: number) => {
        event({
            event: "sales_funnel_step_view",
            funnel_step: index + 1,
            funnel_step_name: stepLabels[index],
        });
    }, []);

    useEffect(() => {
        try {
            const stored = localStorage.getItem(SALES_FUNNEL_STORAGE_KEY);
            if (stored) {
                const saved = JSON.parse(stored) as {
                    data?: Partial<FunnelData>;
                    lastCompletedStep?: string;
                    step?: number;
                    updatedAt?: number;
                };
                if (saved.updatedAt && Date.now() - saved.updatedAt <= 7 * 24 * 60 * 60 * 1000) {
                    setData((current) => ({ ...current, ...saved.data }));
                    if (Number.isInteger(saved.step) && saved.step! >= 0 && saved.step! < 6) {
                        setStep(saved.step!);
                    }
                    const completed = Number(saved.lastCompletedStep);
                    if (Number.isInteger(completed) && completed >= 0 && completed < 6) {
                        setLastCompletedStep(String(completed));
                    }
                } else {
                    localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
                }
            }
        } catch {
            localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
        }
        setRestored(true);
    }, []);

    useEffect(() => {
        if (!restored) {
            return;
        }

        if (skipInitialPersistence.current) {
            skipInitialPersistence.current = false;
            return;
        }

        if (clearPersistence.current) {
            clearPersistence.current = false;
            localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
            return;
        }

        persistSalesFunnelState(localStorage, {
            data,
            lastCompletedStep,
            step,
            updatedAt: Date.now(),
        });
    }, [data, lastCompletedStep, restored, step]);

    useEffect(() => {
        if (restored) {
            trackView(step);
        }
    }, [restored, step, trackView]);

    useEffect(() => {
        const trackAbandonment = (prefetch = false) => {
            if (
                !shouldTrackSalesFunnelAbandonment({
                    alreadyTracked: abandonmentTracked.current,
                    prefetch,
                    step,
                    submitted: submitted.current,
                })
            ) {
                return;
            }

            abandonmentTracked.current = true;
            event({
                event: "sales_funnel_abandonment",
                funnel_step: step + 1,
                funnel_step_name: stepLabels[step],
            });
        };
        const onBeforeUnload = () => trackAbandonment();
        const onVisibility = () => document.hidden && trackAbandonment();
        const removeBeforeListener = router.on("before", (inertiaEvent) =>
            trackAbandonment(inertiaEvent.detail.visit.prefetch),
        );

        window.addEventListener("beforeunload", onBeforeUnload);
        document.addEventListener("visibilitychange", onVisibility);

        return () => {
            removeBeforeListener();
            window.removeEventListener("beforeunload", onBeforeUnload);
            document.removeEventListener("visibilitychange", onVisibility);
        };
    }, [step]);

    const change = (field: keyof FunnelData, value: string) => {
        setData((current) => ({ ...current, [field]: value }));
        setErrors((current) => {
            const next = { ...current };
            delete next[field];
            return next;
        });
    };

    const validate = () => {
        const next: Record<string, string> = {};
        if (step === 0 && !data.product) next.product = "Selecteer een optie.";
        if (step === 1 && !data.description.trim()) next.description = "Vul een omschrijving in.";
        else if (step === 1 && data.description.trim().length < 150)
            next.description = `Omschrijving moet minimaal 150 tekens bevatten (${data.description.trim().length}/150).`;
        if (step === 2 && !data.budget) next.budget = "Selecteer een budgetrange.";
        if (step === 3 && !data.company_type) next.company_type = "Selecteer een voorkeur.";
        if (step === 4) {
            if (!data.name.trim()) next.name = "Vul je naam in.";
            if (!data.company_name.trim()) next.company_name = "Vul je bedrijfsnaam in.";
            if (!data.email.trim()) next.email = "Vul je e-mailadres in.";
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email))
                next.email = "Vul een geldig e-mailadres in.";
        }
        setErrors(next);
        return Object.keys(next).length === 0;
    };

    const next = () => {
        if (!validate()) return;
        event({
            event: "sales_funnel_step_complete",
            funnel_step: step + 1,
            funnel_step_name: stepLabels[step],
        });
        const completedStep = Math.min(5, step + 1);
        setStep(completedStep);
        setLastCompletedStep(String(completedStep));
    };

    const reset = () => {
        clearPersistence.current = true;
        setStep(0);
        setLastCompletedStep("0");
        setData(emptyData);
        setErrors({});
        localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
    };

    const trackSubmit = () => {
        if (!shouldTrackSalesFunnelSubmit(submitted.current)) {
            return;
        }

        submitted.current = true;
        localStorage.removeItem(SALES_FUNNEL_STORAGE_KEY);
        event({
            event: "sales_funnel_submit",
            funnel_product: productLabel,
            funnel_budget: budgetLabel,
            funnel_company_type: companyTypeLabel,
        });
    };

    return (
        <form action={form.action} method="post">
            <input type="hidden" name="_token" value={csrfToken} autoComplete="off" />
            <input type="hidden" name="_redirect" value="/aanvraag/bedankt" />
            <input type="hidden" name="_error_redirect" value="/aanvraag" />
            <input type="hidden" name="product" value={data.product} />
            <input type="hidden" name="product_label" value={productLabel} />
            <input type="hidden" name="budget" value={data.budget} />
            <input type="hidden" name="budget_label" value={budgetLabel} />
            <input type="hidden" name="company_type" value={data.company_type} />
            <input type="hidden" name="company_type_label" value={companyTypeLabel} />
            <input type="hidden" name="name" value={data.name} />
            <input type="hidden" name="company_name" value={data.company_name} />
            <input type="hidden" name="last_completed_step" value={lastCompletedStep} />
            <input
                type="text"
                className="hidden"
                name={form.honeypot ?? "honeypot"}
                tabIndex={-1}
                autoComplete="off"
            />

            <div className="w-full pb-10 lg:pb-16">
                <section className="dlf-wizard-progress" aria-label="Voortgang aanvraag">
                    <div className="dlf-wizard-inner w-full max-w-2xl mx-auto px-6 pt-10 pb-6 sm:px-10 md:px-14 lg:pt-16 lg:pb-10">
                        <div className="flex gap-1.5">
                            {stepLabels.map((label, index) => (
                                <div
                                    key={label}
                                    className={`h-1.5 flex-1 rounded-[2px] transition-colors duration-400 ${index <= step ? "bg-primary-accent" : "bg-gray-200"}`}
                                />
                            ))}
                        </div>
                    </div>
                </section>

                {step === 0 ? (
                    <WizardStep
                        heading="Waar kunnen we je mee helpen?"
                        introduction="Door een vijftal korte vragen te beantwoorden, helpen we je om een goede Laravel partner voor je project te selecteren. Op basis van je antwoorden mailen we je geheel vrijblijvend onze selectie van de beste Laravel specialisten. Kies allereerst de opties die het beste bij jouw vraag passen."
                        id="wizard-product-heading"
                    >
                        <div
                            className="grid grid-cols-1 sm:grid-cols-2 gap-3"
                            role="radiogroup"
                            aria-label="Product keuze"
                        >
                            {products.map(([value, label]) => (
                                <Choice
                                    key={value}
                                    selected={data.product === value}
                                    onClick={() =>
                                        change("product", data.product === value ? "" : value)
                                    }
                                >
                                    <span
                                        className="dlf-wizard-checkbox mt-0.5 flex-shrink-0 w-5 h-5 rounded-[2px] border-[1.5px] flex items-center justify-center transition-colors"
                                        aria-hidden="true"
                                    >
                                        {data.product === value ? (
                                            <svg
                                                className="w-3 h-3 text-white"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        ) : null}
                                    </span>
                                    <span className="dlf-wizard-choice-label font-semibold text-tertiary-dark text-sm leading-snug">
                                        {label}
                                    </span>
                                </Choice>
                            ))}
                        </div>
                        <ErrorMessage message={errors.product} />
                    </WizardStep>
                ) : null}

                {step === 1 ? (
                    <WizardStep
                        heading="Omschrijving van het vraagstuk"
                        introduction="Vertel ons meer over het project dat je in gedachte hebt. Hoe meer informatie je ons geeft, hoe beter wij onze leden kunnen matchen."
                        id="wizard-description-heading"
                    >
                        <label htmlFor="funnel-description" className="sr-only">
                            Omschrijving
                        </label>
                        <textarea
                            id="funnel-description"
                            value={data.description}
                            onChange={(e) => change("description", e.target.value)}
                            name="description"
                            rows={6}
                            placeholder="Beschrijf je project of vraag…"
                            className="dlf-form-field w-full"
                            autoComplete="off"
                        />
                        <div className="flex justify-between items-start mt-2">
                            <ErrorMessage message={errors.description} className="" />
                            <p
                                className={`text-xs text-tertiary-light !mb-0 ml-auto tabular-nums ${data.description.length >= 150 ? "text-green-600" : ""}`}
                            >
                                {data.description.length} / 150
                            </p>
                        </div>
                    </WizardStep>
                ) : null}

                {step === 2 ? (
                    <WizardStep
                        heading="Wat is je budget?"
                        introduction="Ook het budget dat beschikbaar is kan invloed hebben op onze selectie. Selecteer de budgetrange die je in gedachte hebt voor dit project."
                        id="wizard-budget-heading"
                    >
                        <div
                            className="grid grid-cols-1 sm:grid-cols-2 gap-3"
                            role="radiogroup"
                            aria-label="Budget selectie"
                        >
                            {budgets.map(([value, label]) => (
                                <Choice
                                    key={value}
                                    centered
                                    selected={data.budget === value}
                                    onClick={() => change("budget", value)}
                                >
                                    <span className="dlf-wizard-choice-label block text-lg font-semibold text-tertiary-dark">
                                        {label}
                                    </span>
                                </Choice>
                            ))}
                        </div>
                        <ErrorMessage message={errors.budget} />
                    </WizardStep>
                ) : null}

                {step === 3 ? (
                    <WizardStep
                        heading="Heb je een voorkeur voor een type partner?"
                        introduction="Met welke partij werk je het liefst samen."
                        id="wizard-partner-heading"
                    >
                        <div
                            className="grid grid-cols-1 gap-3"
                            role="radiogroup"
                            aria-label="Partnertype selectie"
                        >
                            {companyTypes.map(([value, label, description]) => (
                                <Choice
                                    key={value}
                                    centered
                                    selected={data.company_type === value}
                                    onClick={() => change("company_type", value)}
                                >
                                    <span>
                                        <span className="dlf-wizard-choice-label block text-lg font-semibold text-tertiary-dark">
                                            {label}
                                        </span>
                                        <span className="dlf-wizard-choice-description block text-sm text-tertiary-light mt-1">
                                            {description}
                                        </span>
                                    </span>
                                </Choice>
                            ))}
                        </div>
                        <ErrorMessage message={errors.company_type} />
                    </WizardStep>
                ) : null}

                {step === 4 ? (
                    <WizardStep
                        heading="Contactgegevens"
                        introduction="We vragen je tot slot om je gegevens in te vullen, zodat we je de beste matches kunnen mailen."
                        id="wizard-contact-heading"
                    >
                        <div className="space-y-8">
                            <ContactInput
                                label="Naam"
                                id="funnel-name"
                                value={data.name}
                                onChange={(value) => change("name", value)}
                                error={errors.name}
                                autoComplete="name"
                                placeholder="Je volledige naam"
                            />
                            <ContactInput
                                label="Bedrijfsnaam"
                                id="funnel-company"
                                value={data.company_name}
                                onChange={(value) => change("company_name", value)}
                                error={errors.company_name}
                                autoComplete="organization"
                                placeholder="Je bedrijfsnaam"
                            />
                            <ContactInput
                                label="E-mailadres"
                                id="funnel-email"
                                value={data.email}
                                onChange={(value) => change("email", value)}
                                error={errors.email}
                                autoComplete="email"
                                placeholder="naam@bedrijf.nl"
                                type="email"
                                name="email"
                            />
                        </div>
                    </WizardStep>
                ) : null}

                {step === 5 ? (
                    <WizardStep
                        heading="Overzicht van je aanvraag"
                        introduction="Controleer je gegevens en verstuur je aanvraag."
                        id="wizard-overview-heading"
                    >
                        <div className="border border-gray-200 divide-y divide-gray-200">
                            {[
                                ["Product", productLabel, 0],
                                ["Omschrijving", data.description, 1],
                                ["Budget", budgetLabel, 2],
                                ["Voorkeur partnertype", companyTypeLabel, 3],
                                ["Naam", data.name, 4],
                                ["Bedrijfsnaam", data.company_name, 4],
                                ["E-mailadres", data.email, 4],
                            ].map(([label, value, target]) => (
                                <div
                                    className="flex justify-between items-start p-4 sm:p-5"
                                    key={String(label)}
                                >
                                    <div className="min-w-0">
                                        <span className="block text-xs font-semibold uppercase tracking-wider text-tertiary-light">
                                            {label}
                                        </span>
                                        <span className="block text-tertiary-dark font-semibold mt-1 break-words">
                                            {value}
                                        </span>
                                    </div>
                                    <Button
                                        type="button"
                                        onClick={() => setStep(Number(target))}
                                        className="text-primary-accent text-sm font-semibold hover:underline flex-shrink-0 ml-4 focus-visible:ring-2 focus-visible:ring-primary-accent rounded outline-none"
                                    >
                                        Wijzig
                                    </Button>
                                </div>
                            ))}
                        </div>
                        <div className="mt-6">
                            <Turnstile siteKey={captchaSiteKey} />
                        </div>
                    </WizardStep>
                ) : null}

                <div className="dlf-wizard-navigation w-full py-14 sm:py-16 md:py-20">
                    <div className="max-w-2xl mx-auto px-6 pb-0 sm:px-10 md:px-14 flex justify-between items-center">
                        {step > 0 ? (
                            <div className="dlf-wizard-secondary-actions flex items-center gap-4">
                                <Button
                                    type="button"
                                    onClick={() => setStep((current) => Math.max(0, current - 1))}
                                    className="inline-flex items-center gap-1 text-sm font-semibold text-tertiary-dark hover:text-primary-accent transition-colors px-4 py-2.5 -ml-4 focus-visible:ring-2 focus-visible:ring-primary-accent rounded outline-none"
                                >
                                    <svg
                                        className="relative top-px w-4 h-4 shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M15 19l-7-7 7-7"
                                        />
                                    </svg>
                                    Vorige
                                </Button>
                                <Button
                                    type="button"
                                    onClick={reset}
                                    className="text-sm text-tertiary-light hover:text-primary-accent transition-colors focus-visible:ring-2 focus-visible:ring-primary-accent rounded outline-none"
                                >
                                    Opnieuw beginnen
                                </Button>
                            </div>
                        ) : (
                            <div className="dlf-wizard-navigation-spacer" />
                        )}
                        {step < 5 ? (
                            <DlfButton
                                type="button"
                                onClick={next}
                                className="dlf-wizard-primary-action ml-auto"
                                face="red"
                                shadow="red"
                            >
                                Volgende
                            </DlfButton>
                        ) : (
                            <DlfButton
                                type="submit"
                                onClick={trackSubmit}
                                className="dlf-wizard-primary-action ml-auto"
                                face="red"
                                shadow="red"
                            >
                                Verstuur aanvraag
                            </DlfButton>
                        )}
                    </div>
                </div>
            </div>
        </form>
    );
}

function WizardStep({
    children,
    heading,
    id,
    introduction,
}: {
    children: React.ReactNode;
    heading: string;
    id: string;
    introduction: string;
}) {
    return (
        <div className="dlf-wizard-step">
            <section className="dlf-wizard-intro" aria-labelledby={id}>
                <div className="dlf-wizard-inner max-w-2xl mx-auto px-6 pt-4 sm:px-10 sm:pt-5 md:px-14 md:pt-6">
                    <h2 id={id} className="!mt-0 !mb-2 text-wrap-balance">
                        {heading}
                    </h2>
                    <p className="text-tertiary-light !mb-0">{introduction}</p>
                </div>
            </section>
            <div className="dlf-wizard-body max-w-2xl mx-auto px-6 pt-8 pb-9 sm:px-10 sm:pt-[2.375rem] sm:pb-[2.625rem] md:px-14">
                {children}
            </div>
        </div>
    );
}

function Choice({
    centered = false,
    children,
    onClick,
    selected,
}: {
    centered?: boolean;
    children: React.ReactNode;
    onClick: () => void;
    selected: boolean;
}) {
    return (
        <Button
            type="button"
            role="radio"
            aria-checked={selected}
            onClick={onClick}
            className={`relative rounded border-[1.5px] transition-colors focus-visible:ring-2 focus-visible:ring-primary-accent focus-visible:ring-offset-2 outline-none ${centered ? "p-6 text-center" : "p-4 sm:p-5 text-left flex items-start"} ${selected ? "dlf-wizard-choice--selected border-primary-accent" : "border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50"}`}
        >
            <span className={centered ? "block" : "flex items-start gap-3"}>{children}</span>
        </Button>
    );
}

function ErrorMessage({ className = "mt-3", message }: { className?: string; message?: string }) {
    return message ? (
        <p className={`text-primary-accent text-sm ${className} !mb-0`} role="alert">
            {message}
        </p>
    ) : null;
}

function ContactInput({
    autoComplete,
    error,
    id,
    label,
    name,
    onChange,
    placeholder,
    type = "text",
    value,
}: {
    autoComplete: string;
    error?: string;
    id: string;
    label: string;
    name?: string;
    onChange: (value: string) => void;
    placeholder: string;
    type?: string;
    value: string;
}) {
    return (
        <div>
            <label htmlFor={id} className="dlf-form-label">
                {label}
            </label>
            <Input
                type={type}
                id={id}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                name={name}
                placeholder={placeholder}
                autoComplete={autoComplete}
                spellCheck={type === "email" ? false : undefined}
                className="dlf-form-field w-full"
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${id}-error` : undefined}
            />
            {error ? (
                <p
                    id={`${id}-error`}
                    className="text-primary-accent text-sm mt-1 !mb-0"
                    role="alert"
                >
                    {error}
                </p>
            ) : null}
        </div>
    );
}
