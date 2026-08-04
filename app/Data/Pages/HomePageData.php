<?php

declare(strict_types=1);

namespace App\Data\Pages;

use App\Data\SeoData;
use App\Data\SiteShell\CtaData;
use Spatie\LaravelData\Data;

final class HomePageData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $uri,
        public readonly ?string $headerTitle,
        public readonly ?string $headerContent,
        public readonly ?string $menuTheme,
        public readonly ?CtaData $footerCta,
        public readonly SeoData $seo,
    ) {}
}
