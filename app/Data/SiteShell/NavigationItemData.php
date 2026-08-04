<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class NavigationItemData extends Data
{
    /** @param array<int, NavigationItemData> $children */
    public function __construct(
        public readonly string $id,
        public readonly ?string $title,
        public readonly ?string $slug,
        public readonly ?string $url,
        public readonly ?string $permalink,
        public readonly bool $isCurrent,
        public readonly bool $isAncestor,
        public readonly array $children,
    ) {}
}
