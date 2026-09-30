<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class FoundingPartnerData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $city,
        public readonly ?string $province,
        public readonly ?AssetData $logo,
    ) {}
}
