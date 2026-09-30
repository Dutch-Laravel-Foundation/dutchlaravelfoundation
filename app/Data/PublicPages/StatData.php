<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class StatData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $value,
        public readonly string $label,
        public readonly ?string $context,
    ) {}
}
