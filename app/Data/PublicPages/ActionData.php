<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Data;

final class ActionData extends Data
{
    public function __construct(
        public readonly string $label,
        public readonly LinkData $link,
    ) {}
}
