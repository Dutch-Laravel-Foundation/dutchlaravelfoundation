<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class SiteData extends Data
{
    public function __construct(
        public readonly string $handle,
        public readonly string $name,
        public readonly string $locale,
        public readonly string $shortLocale,
        public readonly string $url,
    ) {}
}
