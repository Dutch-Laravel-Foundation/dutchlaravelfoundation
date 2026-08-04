<?php

declare(strict_types=1);

namespace App\Data\PublicPages;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class ContentBlockData extends Data
{
    /**
     * @param  array<int, ContentBlockData>  $left
     * @param  array<int, ContentBlockData>  $right
     * @param  array<int, ContentBlockData>  $content
     * @param  array<int, FeatureData>  $features
     * @param  array<int, CardData>  $cards
     * @param  array<int, StatData>  $stats
     * @param  array<int, LogoData>  $logos
     * @param  array<int, PricingPlanData>  $plans
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $type,
        public readonly ?string $id = null,
        public readonly ?string $html = null,
        public readonly ?string $headingHtml = null,
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $left = [],
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $right = [],
        #[DataCollectionOf(ContentBlockData::class)]
        public readonly array $content = [],
        public readonly ?AssetData $asset = null,
        public readonly ?string $title = null,
        public readonly ?string $text = null,
        public readonly ?string $value = null,
        public readonly ?string $label = null,
        public readonly ?LinkData $link = null,
        public readonly ?string $eyebrow = null,
        public readonly ?string $heading = null,
        public readonly ?string $bodyHtml = null,
        public readonly ?string $introductionHtml = null,
        public readonly ?string $columns = null,
        public readonly ?string $headingLevel = null,
        public readonly ?string $imagePosition = null,
        public readonly ?string $tone = null,
        public readonly ?ActionData $primaryAction = null,
        public readonly ?ActionData $secondaryAction = null,
        #[DataCollectionOf(FeatureData::class)]
        public readonly array $features = [],
        #[DataCollectionOf(CardData::class)]
        public readonly array $cards = [],
        #[DataCollectionOf(StatData::class)]
        public readonly array $stats = [],
        #[DataCollectionOf(LogoData::class)]
        public readonly array $logos = [],
        #[DataCollectionOf(PricingPlanData::class)]
        public readonly array $plans = [],
        public readonly ?string $quote = null,
        public readonly ?string $attributionName = null,
        public readonly ?string $attributionRole = null,
    ) {}
}
