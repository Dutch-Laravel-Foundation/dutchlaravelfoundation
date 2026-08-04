<?php

declare(strict_types=1);

namespace App\Data\Home;

use Spatie\LaravelData\Data;

final class ClientData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?AssetData $logo,
    ) {}
}
