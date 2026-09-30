<?php

declare(strict_types=1);

namespace App\Data\Home;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class HomeData extends Data
{
    /**
     * @param  array<int, PartnerData>  $partners
     * @param  array<int, ClientData>  $clients
     */
    public function __construct(
        public readonly ?ContentCardData $latestInsight,
        public readonly ?ContentCardData $latestKnowledge,
        public readonly ?ContentCardData $highlightedInsight,
        #[DataCollectionOf(PartnerData::class)]
        public readonly array $partners,
        #[DataCollectionOf(ClientData::class)]
        public readonly array $clients,
    ) {}
}
