<?php

declare(strict_types=1);

namespace App\Data\Community;

use App\Data\SeoData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class PageData extends Data
{
    /** @param array<int, ContentBlockData> $content */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $template,
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $content,
        public readonly ?CallToActionData $callToAction,
        public readonly SeoData $seo,
    ) {}
}
