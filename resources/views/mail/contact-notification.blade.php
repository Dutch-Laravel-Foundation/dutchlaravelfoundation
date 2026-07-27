@php
    $mailValue = static function (mixed $value): string {
        return str_replace(
            ['\\', '|', "\r", "\n"],
            ['\\\\', '\\|', ' ', ' '],
            trim((string) $value),
        );
    };
@endphp
<x-mail::message>
# Nieuw contactbericht

Er is een nieuw contactbericht binnengekomen via de website.

<x-mail::table>
| Onderdeel | Ingevuld |
| :--- | :--- |
| **Naam** | {{ $mailValue($name) }} |
| **Bedrijfsnaam** | {{ $mailValue($company_name) }} |
| **E-mailadres** | {{ $mailValue($email) }} |
| **Opmerkingen** | {{ $mailValue($remarks) }} |
</x-mail::table>
</x-mail::message>
