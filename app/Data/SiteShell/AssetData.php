<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class AssetData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $url,
        public readonly ?string $permalink,
        public readonly ?float $width,
        public readonly ?float $height,
    ) {}
}
