<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class PublicPageSupportData extends Data
{
    /**
     * @param  array<int, BoardMemberData>  $board
     * @param  array<int, FoundingPartnerData>  $foundingPartners
     * @param  array<int, LandingCaseData>  $generalLandingCases
     * @param  array<int, LandingCaseData>  $frameworkLandingCases
     */
    public function __construct(
        public readonly int $memberCount,
        #[DataCollectionOf(BoardMemberData::class)]
        public readonly array $board,
        #[DataCollectionOf(FoundingPartnerData::class)]
        public readonly array $foundingPartners,
        #[DataCollectionOf(LandingCaseData::class)]
        public readonly array $generalLandingCases,
        #[DataCollectionOf(LandingCaseData::class)]
        public readonly array $frameworkLandingCases,
    ) {}
}
