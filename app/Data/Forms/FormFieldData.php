<?php

declare(strict_types=1);

namespace App\Data\Forms;

use Spatie\LaravelData\Data;

final class FormFieldData extends Data
{
    /**
     * @param  array<string, mixed>  $ifConditions
     * @param  array<string, mixed>  $unlessConditions
     * @param  array<string, mixed>  $config
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
