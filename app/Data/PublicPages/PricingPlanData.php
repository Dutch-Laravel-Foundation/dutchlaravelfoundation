<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class PricingPlanData extends Data
{
    /** @param array<int, string> $features */
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $price,
        public readonly ?string $suffix,
        public readonly ?string $descriptionHtml,
        public readonly array $features,
        public readonly ?ActionData $action,
        public readonly bool $featured,
    ) {}
}
