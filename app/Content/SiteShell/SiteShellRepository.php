<?php

declare(strict_types=1);

namespace App\Content\SiteShell;

interface SiteShellRepository
{
    /** @return array<string, mixed> */
    public function fetch(): array;
}
