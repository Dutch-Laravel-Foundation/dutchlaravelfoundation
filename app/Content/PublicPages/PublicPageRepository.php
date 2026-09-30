<?php

declare(strict_types=1);

namespace App\Content\PublicPages;

interface PublicPageRepository
{
    public const array GENERAL_LANDING_CASE_SLUGS = [
        'dropday',
        'de-recyclingsindustrie-digitaliseren-met-laravel',
        'teamflow-app',
    ];

    public const array FRAMEWORK_LANDING_CASE_SLUGS = [
        'diabetes-nl-helpt-je-verder-weten-delen-doen',
        'platform-voor-recycling-printercartridges',
    ];

    public const array LANDING_CASE_SLUGS = [
        ...self::GENERAL_LANDING_CASE_SLUGS,
        ...self::FRAMEWORK_LANDING_CASE_SLUGS,
    ];

    /** @return array<string, mixed> */
    public function findByUri(string $uri): array;
}
