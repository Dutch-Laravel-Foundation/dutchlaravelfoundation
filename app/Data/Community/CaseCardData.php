<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class CaseCardData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $displayTitle,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $date,
        public readonly ?string $introductionHtml,
        public readonly ?AssetData $featuredImage,
        public readonly ?MemberSummaryData $member,
        public readonly ?ClientData $client,
    ) {}
}
