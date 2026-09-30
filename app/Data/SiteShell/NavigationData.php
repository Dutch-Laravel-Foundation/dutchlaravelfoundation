<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class NavigationData extends Data
{
    /**
     * @param  array<int, NavigationItemData>  $main
     * @param  array<int, NavigationItemData>  $legal
     */
    public function __construct(
        public readonly array $main,
        public readonly array $legal,
    ) {}
}
