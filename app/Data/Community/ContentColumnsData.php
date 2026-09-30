<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class ContentColumnsData extends Data
{
    /**
     * @param  array<int, ContentBlockData>  $left
     * @param  array<int, ContentBlockData>  $right
     */
    public function __construct(
        public readonly ?string $headingHtml,
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $left,
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $right,
    ) {}
}
