<?php

namespace Facades\Statamic\CP;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\CP\LivePreview
 */
class LivePreview extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\CP\LivePreview';
    }
}
