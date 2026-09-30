<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Data;

final class CallToActionData extends Data
{
    /** @param array<int, string> $benefits */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $eyebrow,
        public readonly array $benefits,
        public readonly ?LinkData $link,
        public readonly ?LinkData $secondaryLink,
        public readonly ?string $theme,
        public readonly ?string $buttonText,
        public readonly ?string $buttonStyle,
        public readonly ?string $secondaryButtonText,
        public readonly ?string $secondaryButtonStyle,
    ) {}
}
