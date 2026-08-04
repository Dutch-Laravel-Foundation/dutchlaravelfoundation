<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use Spatie\LaravelData\Data;

final class PaginationData extends Data
{
    public function __construct(
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage,
        public readonly ?int $from,
        public readonly ?int $to,
        public readonly int $lastPage,
        public readonly bool $hasMorePages,
    ) {}
}
