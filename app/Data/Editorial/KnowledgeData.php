<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use App\Data\SeoData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class KnowledgeData extends Data
{
    /** @param array<int, AuthorData> $authors */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $category,
        public readonly ?string $date,
        public readonly ?string $introduction,
        public readonly ?AssetData $featuredImage,
        public readonly ?string $contentHtml,
        #[DataCollectionOf(AuthorData::class)]
        public readonly array $authors,
        public readonly ?CallToActionData $callToAction,
        public readonly SeoData $seo,
    ) {}
}
