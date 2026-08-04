<?php

declare(strict_types=1);

namespace App\Data\Community;

use App\Data\SeoData;
use Spatie\LaravelData\Data;

final class InternshipData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $descriptionHtml,
        public readonly ?string $applyUrl,
        public readonly ?string $applyLabel,
        public readonly MemberSummaryData $member,
        public readonly SeoData $seo,
    ) {}
}
