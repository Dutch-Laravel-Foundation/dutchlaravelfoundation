<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class LinkData extends Data
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $title,
    ) {}
}
