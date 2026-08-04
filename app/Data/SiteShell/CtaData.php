<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class CtaData extends Data
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
        public readonly ?LabeledValueData $theme,
        public readonly ?LabeledValueData $buttonStyle,
        public readonly ?LabeledValueData $secondaryButtonStyle,
        public readonly ?string $buttonText,
        public readonly ?string $secondaryButtonText,
    ) {}
}
