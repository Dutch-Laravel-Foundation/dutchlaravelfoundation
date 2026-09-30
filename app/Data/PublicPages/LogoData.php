<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class LogoData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?AssetData $asset,
        public readonly ?LinkData $link,
    ) {}
}
