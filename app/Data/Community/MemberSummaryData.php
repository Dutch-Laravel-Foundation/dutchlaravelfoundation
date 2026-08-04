<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class MemberSummaryData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?AssetData $logo,
        public readonly ?string $type,
        public readonly ?string $employeeRange,
        public readonly bool $sbb,
        public readonly ?string $city,
        public readonly ?string $province,
        public readonly ?string $website,
        public readonly ?InternshipContactData $internshipContact,
    ) {}
}
