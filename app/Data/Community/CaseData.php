<?php

declare(strict_types=1);

namespace App\Data\Community;

use App\Data\SeoData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class CaseData extends Data
{
    /** @param array<int, ContentBlockData> $content */
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
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $content,
        public readonly ?MemberSummaryData $member,
        public readonly ?ClientData $client,
        public readonly SeoData $seo,
    ) {}
}
