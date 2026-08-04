<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use Spatie\LaravelData\Data;

final class FooterData extends Data
{
    /**
     * @param  array<int, MemberData>  $members
     * @param  array<int, SocialData>  $socials
     */
    public function __construct(
        public readonly array $members,
        public readonly array $socials,
    ) {}
}
