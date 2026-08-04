<?php

declare(strict_types=1);

namespace App\Content\Home;

interface HomeRepository
{
    public const array CURATED_CLIENT_SLUGS = [
        'de-verbouwcalculator',
        'intersafe',
        'dropday',
        'mobiliteitsfabriek',
        'eurosafe',
        'avia',
        'abn-amro',
        'recirculo',
        'flow-concepts',
        'recoll',
        'race-planet',
        'stichting-studiekeuze123',
        'youngwize',
        'nva',
        'inventum',
    ];

    /** @return array<string, mixed> */
    public function get(): array;
}
