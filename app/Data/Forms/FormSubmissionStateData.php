<?php

declare(strict_types=1);

namespace App\Data\Forms;

use Spatie\LaravelData\Data;

final class FormSubmissionStateData extends Data
{
    /**
     * @param  array<string, string>  $errors
     * @param  array<string, mixed>  $old
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $errors,
        public readonly array $old,
    ) {}
}
