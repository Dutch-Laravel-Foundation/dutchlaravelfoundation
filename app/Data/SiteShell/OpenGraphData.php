<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class OpenGraphData extends Data
{
    public function __construct(public readonly ?AssetData $image) {}
}
