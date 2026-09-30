<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class MemberFiltersData extends Data
{
    /**
     * @param  array<int, string>  $types
     * @param  array<int, string>  $employeeRanges
     * @param  array<int, string>  $provinces
     */
    public function __construct(
        public readonly array $types,
        public readonly array $employeeRanges,
        public readonly array $provinces,
    ) {}
}
