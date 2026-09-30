<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class OrganizationData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $address,
        public readonly ?string $zipcode,
        public readonly ?string $city,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $coc,
        public readonly ?AssetData $logo,
        public readonly SiteData $site,
    ) {}
}
