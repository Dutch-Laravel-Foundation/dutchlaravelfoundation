<?php

namespace Facades\Statamic\Routing;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Routing\ResolveRedirect
 */
class ResolveRedirect extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Routing\ResolveRedirect';
    }
}
