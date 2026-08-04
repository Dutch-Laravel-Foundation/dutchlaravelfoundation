<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class LarabellesData extends Data
{
    public function __construct(public readonly PageData $page) {}
}
