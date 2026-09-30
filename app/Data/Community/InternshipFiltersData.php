<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class InternshipFiltersData extends Data
{
    /** @param array<int, string> $provinces */
    public function __construct(
        public readonly array $provinces,
        public readonly bool $hasSbb,
    ) {}
}
