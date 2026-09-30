<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class PodcastIndexData extends Data
{
    /** @param array<int, PodcastCardData> $items */
    public function __construct(
        #[DataCollectionOf(PodcastCardData::class)]
        public readonly array $items,
        public readonly PaginationData $pagination,
    ) {}
}
