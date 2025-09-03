<?php

namespace Facades\Statamic\Tokens;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Tokens\Generator
 */
class Generator extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Tokens\Generator';
    }
}
