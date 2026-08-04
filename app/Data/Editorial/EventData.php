<?php

declare(strict_types=1);

namespace App\Data\Editorial;

use App\Data\SeoData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class EventData extends Data
{
    /** @param array<int, ContentBlockData> $content */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $url,
        public readonly ?string $uri,
        public readonly ?string $type,
        public readonly ?string $introduction,
        public readonly ?AssetData $featuredImage,
        public readonly ?string $dateStart,
        public readonly ?string $timeStart,
        public readonly ?string $timeEnd,
        public readonly ?string $location,
        public readonly ?string $address,
        public readonly ?string $signupLink,
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $content,
        public readonly SeoData $seo,
    ) {}
}
