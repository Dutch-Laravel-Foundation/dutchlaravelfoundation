<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class NewsletterFieldData extends Data
{
    /**
     * @param  array<int|string, mixed>  $ifConditions
     * @param  array<int|string, mixed>  $unlessConditions
     * @param  array<int|string, mixed>  $config
     */
    public function __construct(
        public readonly string $handle,
        public readonly string $type,
        public readonly string $display,
        public readonly ?string $instructions,
        public readonly ?int $width,
        public readonly array $ifConditions,
        public readonly array $unlessConditions,
        public readonly array $config,
    ) {}
}
