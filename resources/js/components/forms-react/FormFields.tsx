import { Button } from "@base-ui/react/button";
import { Input } from "@base-ui/react/input";

import { Turnstile } from "@/components/site/Turnstile";
import { DlfButton } from "@/components/ui/DlfButton";
import { SmartLink } from "@/components/ui/SmartLink";

import { type FormDefinitionData, type FormSubmissionStateData } from "./types";

type FormFieldsProps = {
    captchaSiteKey?: string | null;
    csrfToken: string;
    form: FormDefinitionData;
    redirect: string;
    submission: FormSubmissionStateData;
    variant: "community" | "public";
};

function oldValue(submission: FormSubmissionStateData, handle: string): string {
    const value = submission.old[handle];

    return typeof value === "string" || typeof value === "number" ? String(value) : "";
}

function PrivacyLabel({ display }: { display: string }) {
    const phrase = "privacy statement";
    const position = display.toLocaleLowerCase("nl").indexOf(phrase);

    if (position === -1) {
        return display;
    }

    return (
        <>
            {display.slice(0, position)}
            <SmartLink href="/privacy-statement" target="_blank" rel="noreferrer">
                {display.slice(position, position + phrase.length)}
            </SmartLink>
            {display.slice(position + phrase.length)}
        </>
    );
}

export function FormFields({
    captchaSiteKey,
    csrfToken,
    form,
    redirect,
    submission,
    variant,
}: FormFieldsProps) {
    return (
        <form action={form.action} method="post">
            <input type="hidden" name="_token" value={csrfToken} autoComplete="off" />
            <input type="hidden" name="_redirect" value={redirect} />
            <input type="hidden" name="_error_redirect" value={redirect} />
            <input
                type="text"
                className="hidden"
                name={form.honeypot ?? "honeypot"}
                tabIndex={-1}
                autoComplete="off"
                aria-hidden="true"
            />

            {Object.keys(submission.errors).length ? (
                <div className="dlf-member-form__errors" role="alert">
                    {variant === "public" ? (
                        <>
                            <strong>Controleer de gemarkeerde velden.</strong>
                            <br />
                        </>
                    ) : null}
                    {Object.values(submission.errors).map((error) => (
                        <span key={error}>
                            {error}
                            <br />
                        </span>
                    ))}
                </div>
            ) : null}

            <div className="dlf-member-form__fields">
                {form.fields.map((field) => {
                    const id = `${form.handle}-form-${field.handle}-field`;
                    const error = submission.errors[field.handle];
                    const rules = form.rules[field.handle] ?? [];
                    const required = rules.includes("required");

                    if (field.type === "toggle") {
                        const old = submission.old[field.handle];
                        const checked = old === true || old === "1" || old === 1 || old === "on";

                        return (
                            <div key={field.handle}>
                                <label className="dlf-member-form__agree" htmlFor={id}>
                                    <input
                                        id={id}
                                        type="checkbox"
                                        name={field.handle}
                                        value="1"
                                        defaultChecked={checked}
                                        required={required}
                                        aria-invalid={Boolean(error)}
                                        aria-describedby={error ? `${id}-error` : undefined}
                                    />
                                    <span>
                                        <PrivacyLabel display={field.display} />
                                    </span>
                                </label>
                                {error ? (
                                    <p id={`${id}-error`} className="dlf-member-form__error">
                                        {error}
                                    </p>
                                ) : null}
                            </div>
                        );
                    }

                    const inputType =
                        typeof field.config.input_type === "string"
                            ? field.config.input_type
                            : field.handle === "email"
                              ? "email"
                              : "text";
                    const placeholder =
                        typeof field.config.placeholder === "string"
                            ? field.config.placeholder
                            : undefined;

                    return (
                        <label key={field.handle} className="dlf-form-group" htmlFor={id}>
                            <span className="dlf-form-label">{field.display}</span>
                            {field.instructions ? <span>{field.instructions}</span> : null}
                            {field.type === "textarea" ? (
                                <textarea
                                    id={id}
                                    className="dlf-form-field"
                                    name={field.handle}
                                    rows={5}
                                    defaultValue={oldValue(submission, field.handle)}
                                    required={required}
                                    aria-invalid={Boolean(error)}
                                    aria-describedby={error ? `${id}-error` : undefined}
                                />
                            ) : (
                                <Input
                                    id={id}
                                    className="dlf-form-field"
                                    name={field.handle}
                                    type={inputType}
                                    defaultValue={oldValue(submission, field.handle)}
                                    placeholder={placeholder}
                                    required={required}
                                    autoComplete={
                                        field.handle === "email"
                                            ? "email"
                                            : field.handle === "phone"
                                              ? "tel"
                                              : field.handle === "name"
                                                ? "name"
                                                : field.handle === "company_name"
                                                  ? "organization"
                                                  : undefined
                                    }
                                    aria-invalid={Boolean(error)}
                                    aria-describedby={error ? `${id}-error` : undefined}
                                />
                            )}
                            {error ? (
                                <p id={`${id}-error`} className="dlf-member-form__error">
                                    {error}
                                </p>
                            ) : null}
                        </label>
                    );
                })}

                <Turnstile siteKey={captchaSiteKey} />

                {variant === "public" ? (
                    <DlfButton
                        type="submit"
                        className="dlf-contact-form__submit"
                        face="red"
                        shadow="red"
                    >
                        Versturen
                    </DlfButton>
                ) : (
                    <Button type="submit" className="dlf-community-button">
                        <span className="dlf-community-button__face">Versturen</span>
                        <span className="dlf-community-button__back" aria-hidden="true" />
                    </Button>
                )}
            </div>
        </form>
    );
}
