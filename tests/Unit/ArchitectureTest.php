<?php

declare(strict_types=1);

arch('application code does not contain debugging statements')
    ->expect(['dd', 'dump', 'var_dump', 'print_r', 'die'])
    ->not->toBeUsed();

arch('content repositories do not depend on Eloquent')
    ->expect('App\\Content')
    ->not->toUse('Illuminate\\Database\\Eloquent');
