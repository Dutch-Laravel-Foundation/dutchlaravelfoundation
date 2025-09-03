<?php

namespace Facades\Statamic\Fieldtypes;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Fieldtypes\RowId
 */
class RowId extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Fieldtypes\RowId';
    }
}
