import { Button } from "@base-ui/react/button";
import { usePage } from "@inertiajs/react";

import { type NewsletterForm as NewsletterFormData } from "./types";
import { Turnstile } from "./Turnstile";

export function NewsletterForm({ form }: { form: NewsletterFormData }) {
    const { captchaSiteKey, csrfToken } = usePage<{
        app: { captchaSiteKey?: string | null; csrfToken: string };
    }>().props.app;

    return (
        <form action={`/!/forms/${form.handle}`} method="post" className="dlf-newsletter-form">
            <input type="hidden" name="_token" value={csrfToken} autoComplete="off" />
            <input type="hidden" name="_redirect" value="/newsletter" />
            <input
                type="text"
                className="dlf-honeypot"
                name={form.honeypot ?? "honeypot"}
                tabIndex={-1}
                autoComplete="off"
                aria-hidden="true"
            />
            <div className="dlf-newsletter-field">
                {form.fields.map((field) => {
                    const inputType =
                        typeof field.config.input_type === "string"
                            ? field.config.input_type
                            : field.type;
                    const placeholder =
                        typeof field.config.placeholder === "string"
                            ? field.config.placeholder
                            : undefined;
                    const rules = form.rules[field.handle];
                    const validation = Array.isArray(rules) ? rules : [];

                    return (
                        <label key={field.handle}>
                            <span className="dlf-sr-only">{field.display}</span>
                            <input
                                id={`${form.handle}-form-${field.handle}-field`}
                                type={inputType}
                                name={field.handle}
                                autoComplete={field.handle === "email" ? "email" : undefined}
                                placeholder={placeholder}
                                required={validation.includes("required")}
                            />
                        </label>
                    );
                })}
                <Button type="submit" aria-label="Aanmelden">
                    <img src="/assets/img/icons/send.svg" alt="" width="16" height="16" />
                </Button>
            </div>
            <Turnstile siteKey={captchaSiteKey} />
        </form>
    );
}
