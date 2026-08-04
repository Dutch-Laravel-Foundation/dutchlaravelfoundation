<?php

declare(strict_types=1);

namespace App\Content\Repositories;

interface PageRepository
{
    /** @return array<string, mixed>|null */
    public function findByUri(string $uri): ?array;
}
