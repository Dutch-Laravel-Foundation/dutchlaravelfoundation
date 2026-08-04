<?php

declare(strict_types=1);

namespace App\Data\Forms;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

final class FormDefinitionData extends Data
{
    /**
     * @param  array<string, array<int, string>>  $rules
     * @param  array<int, FormFieldData>  $fields
     */
    public function __construct(
        public readonly string $handle,
        public readonly string $title,
        public readonly string $action,
        public readonly ?string $honeypot,
        public readonly array $rules,
        #[DataCollectionOf(FormFieldData::class)]
        public readonly array $fields,
    ) {}
}
