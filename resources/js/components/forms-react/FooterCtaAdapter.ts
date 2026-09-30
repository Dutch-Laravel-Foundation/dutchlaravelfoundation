const optionValues: Record<string, string> = {
    donker: "dark",
    licht: "light",
    "licht (alternatief)": "light-alt",
    primair: "primary",
    rood: "red",
};

function option(value: string | null): App.Data.SiteShell.LabeledValueData | null {
    if (!value) {
        return null;
    }

    return {
        label: value,
        value: optionValues[value.toLocaleLowerCase("nl")] ?? value.toLocaleLowerCase("nl"),
    };
}

export function footerCta(
    cta: App.Data.PublicPages.CallToActionData | null,
): App.Data.SiteShell.CtaData | null {
    if (!cta) {
        return null;
    }

    return {
        benefits: cta.benefits,
        buttonStyle: option(cta.buttonStyle),
        buttonText: cta.buttonText,
        description: cta.descriptionHtml,
        eyebrow: cta.eyebrow,
        id: cta.id,
        link: cta.link,
        secondaryButtonStyle: option(cta.secondaryButtonStyle),
        secondaryButtonText: cta.secondaryButtonText,
        secondaryLink: cta.secondaryLink,
        theme: option(cta.theme),
        title: cta.title,
    };
}
