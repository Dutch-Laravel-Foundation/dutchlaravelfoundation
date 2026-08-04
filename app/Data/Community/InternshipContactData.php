<?php

declare(strict_types=1);

namespace App\Data\Community;

use Spatie\LaravelData\Data;

final class InternshipContactData extends Data
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
    ) {}
}
