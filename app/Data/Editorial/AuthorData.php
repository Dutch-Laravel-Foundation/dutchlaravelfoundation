<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Data;

final class AuthorData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $role,
        public readonly ?string $bio,
        public readonly ?AssetData $image,
        public readonly ?string $imageUrl,
        public readonly ?string $profileUrl,
        public readonly ?string $linkedinUrl,
        public readonly ?string $websiteUrl,
    ) {}
}
