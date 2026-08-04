<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class InternshipIndexData extends Data
{
    /** @param array<int, InternshipCardData> $items */
    public function __construct(
        public readonly PageData $page,
        #[DataCollectionOf(InternshipCardData::class)]
        public readonly array $items,
        public readonly InternshipFiltersData $filters,
    ) {}
}
