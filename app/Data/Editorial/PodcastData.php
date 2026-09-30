<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use App\Data\SeoData;
use Spatie\LaravelData\Data;

final class PodcastData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly string $summary,
        public readonly string $descriptionHtml,
        public readonly string $videoUrl,
        public readonly string $spotifyUrl,
        public readonly string $thumbnailUrl,
        public readonly string $transcriptHtml,
        public readonly string $publishedAt,
        public readonly ?CallToActionData $callToAction,
        public readonly SeoData $seo,
    ) {}
}
