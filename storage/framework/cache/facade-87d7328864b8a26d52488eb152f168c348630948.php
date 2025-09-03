<?php

namespace Facades\Statamic\Imaging;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Imaging\Attributes
 */
class Attributes extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Imaging\Attributes';
    }
}
