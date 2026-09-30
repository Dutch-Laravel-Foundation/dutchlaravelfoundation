<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class NewsletterFormData extends Data
{
    /**
     * @param  array<int|string, mixed>  $rules
     * @param  array<int, NewsletterFieldData>  $fields
     */
    public function __construct(
        public readonly string $handle,
        public readonly string $title,
        public readonly ?string $honeypot,
        public readonly array $rules,
        public readonly array $fields,
    ) {}
}
