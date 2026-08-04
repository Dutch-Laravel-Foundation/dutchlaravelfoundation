<?php

declare(strict_types=1);

namespace App\Content\Forms;

interface FormsRepository
{
    /** @return array<string, mixed>|null */
    public function find(string $handle): ?array;
}
