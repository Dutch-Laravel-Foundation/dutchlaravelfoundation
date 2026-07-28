@php
    $mailValue = static function (mixed $value): string {
        return str_replace(
            ['\\', '|', "\r", "\n"],
            ['\\\\', '\\|', ' ', ' '],
            trim((string) $value),
        );
    };

    $productValue = $mailValue($product_label);
    $budgetValue = $mailValue($budget_label);
    $companyTypeValue = $mailValue($company_type_label);
@endphp
<x-mail::message>
# Aanvraag via Dutch Laravel Foundation

Beste {{ $mailValue($name) }},

Bedankt voor je aanvraag. Hieronder staat een overzicht van de gegevens die je hebt ingevuld. Wij nemen zo spoedig mogelijk contact met je op.

<x-mail::table>
| Onderdeel | Ingevuld |
| :--- | :--- |
| **Product** | {{ $productValue !== '' ? $productValue : $mailValue($product) }} |
| **Omschrijving** | {{ $mailValue($description) }} |
| **Budget** | {{ $budgetValue !== '' ? $budgetValue : $mailValue($budget) }} |
| **Voorkeur partnertype** | {{ $companyTypeValue !== '' ? $companyTypeValue : $mailValue($company_type) }} |
| **Naam** | {{ $mailValue($name) }} |
| **Bedrijfsnaam** | {{ $mailValue($company_name) }} |
| **E-mailadres** | {{ $mailValue($email) }} |
</x-mail::table>

<x-mail::subcopy>
Dit bericht is verstuurd naar zowel de aanvrager als Dutch Laravel Foundation, zodat beide partijen het overzicht hebben.
</x-mail::subcopy>
</x-mail::message>
