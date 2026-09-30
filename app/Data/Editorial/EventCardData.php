<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Data;

final class EventCardData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $type,
        public readonly ?string $dateStart,
        public readonly ?string $introduction,
        public readonly ?AssetData $featuredImage,
    ) {}
}
