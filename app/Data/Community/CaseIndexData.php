<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class CaseIndexData extends Data
{
    /** @param array<int, CaseCardData> $items */
    public function __construct(
        public readonly PageData $page,
        #[DataCollectionOf(CaseCardData::class)]
        public readonly array $items,
    ) {}
}
