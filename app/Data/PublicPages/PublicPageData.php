<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use App\Data\SeoData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class PublicPageData extends Data
{
    /** @param array<int, ContentBlockData> $content */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly string $template,
        public readonly ?string $menuTheme,
        public readonly ?string $headerTitle,
        public readonly ?string $headerContentHtml,
        public readonly SeoData $seo,
        public readonly ?CallToActionData $callToAction,
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $content,
        public readonly PublicPageSupportData $support,
    ) {}
}
