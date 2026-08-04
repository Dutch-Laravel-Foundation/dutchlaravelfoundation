<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class AssetData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $url,
        public readonly ?string $permalink,
        public readonly string $path,
        public readonly string $extension,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?string $focusCss,
        public readonly ?string $alt,
    ) {}
}
