<?php

declare(strict_types=1);

namespace App\Data\Community;

use App\Data\SeoData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class MemberData extends Data
{
    /**
     * @param  array<int, InternshipCardData>  $internships
     * @param  array<int, CaseCardData>  $cases
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $descriptionHtml,
        public readonly ?AssetData $logo,
        public readonly bool $foundingPartner,
        public readonly ?string $type,
        public readonly ?string $employeeRange,
        public readonly bool $sbb,
        public readonly ?string $city,
        public readonly ?string $province,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $website,
        public readonly ?string $recruitmentWebsite,
        public readonly ?string $video,
        public readonly ?InternshipContactData $internshipContact,
        public readonly SeoData $seo,
        #[DataCollectionOf(InternshipCardData::class)]
        public readonly array $internships,
        #[DataCollectionOf(CaseCardData::class)]
        public readonly array $cases,
    ) {}
}
