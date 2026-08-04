<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class FeatureData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $heading,
        public readonly ?string $bodyHtml,
        public readonly ?AssetData $icon,
        public readonly ?ActionData $action,
    ) {}
}
