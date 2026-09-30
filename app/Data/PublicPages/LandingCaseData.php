<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class LandingCaseData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $longTitle,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $introductionHtml,
        public readonly ?AssetData $featuredImage,
    ) {}
}
