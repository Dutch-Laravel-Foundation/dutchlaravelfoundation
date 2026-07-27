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
# Nieuwe DLF-aanmelding

Er is een nieuwe aanmelding binnengekomen via de website.

<x-mail::table>
| Onderdeel | Ingevuld |
| :--- | :--- |
| **Naam** | {{ $mailValue($name) }} |
| **E-mailadres** | {{ $mailValue($email) }} |
| **Bedrijfsnaam** | {{ $mailValue($company_name) }} |
| **Telefoonnummer** | {{ $mailValue($phone) }} |
| **Opmerkingen** | {{ $mailValue($remarks) }} |
</x-mail::table>
</x-mail::message>
