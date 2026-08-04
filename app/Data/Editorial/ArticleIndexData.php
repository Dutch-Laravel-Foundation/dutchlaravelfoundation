<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class ArticleIndexData extends Data
{
    /** @param array<int, ArticleCardData> $items */
    public function __construct(
        #[DataCollectionOf(ArticleCardData::class)]
        public readonly array $items,
        public readonly PaginationData $pagination,
    ) {}
}
