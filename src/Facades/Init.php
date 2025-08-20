<?php

namespace Fuelviews\Init\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fuelviews\Init\Init
 */
class Init extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'init';
    }
}
