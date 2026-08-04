<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class MemberIndexData extends Data
{
    /** @param array<int, MemberSummaryData> $items */
    public function __construct(
        public readonly PageData $page,
        #[DataCollectionOf(MemberSummaryData::class)]
        public readonly array $items,
        public readonly MemberFiltersData $filters,
    ) {}
}
