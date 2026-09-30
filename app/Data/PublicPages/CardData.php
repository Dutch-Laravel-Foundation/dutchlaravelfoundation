<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class CardData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $eyebrow,
        public readonly string $heading,
        public readonly ?string $bodyHtml,
        public readonly ?AssetData $image,
        public readonly ?ActionData $action,
    ) {}
}
