<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class SocialData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?LinkData $link,
        public readonly ?AssetData $icon,
    ) {}
}
