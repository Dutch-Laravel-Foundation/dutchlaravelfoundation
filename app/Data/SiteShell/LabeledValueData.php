<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class LabeledValueData extends Data
{
    public function __construct(
        public readonly ?string $value,
        public readonly ?string $label,
    ) {}
}
