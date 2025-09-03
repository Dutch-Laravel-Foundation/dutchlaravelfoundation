<?php

namespace Facades\Statamic\Assets;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Assets\Attributes
 */
class Attributes extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Assets\Attributes';
    }
}
