<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class EventIndexData extends Data
{
    /**
     * @param  array<int, EventCardData>  $upcoming
     * @param  array<int, EventCardData>  $past
     */
    public function __construct(
        #[DataCollectionOf(EventCardData::class)]
        public readonly array $upcoming,
        #[DataCollectionOf(EventCardData::class)]
        public readonly array $past,
        public readonly PaginationData $pagination,
    ) {}
}
