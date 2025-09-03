<?php

namespace Facades\Statamic\Structures;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Structures\CollectionTreeDiff
 */
class CollectionTreeDiff extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Structures\CollectionTreeDiff';
    }
}
