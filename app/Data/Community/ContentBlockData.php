<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class ContentBlockData extends Data
{
    public function __construct(
        public readonly string $kind,
        public readonly string $type,
        public readonly ?string $id,
        public readonly ?string $html,
        public readonly ?string $value,
        public readonly ?AssetData $asset,
        public readonly ?ContentColumnsData $columns,
    ) {}
}
