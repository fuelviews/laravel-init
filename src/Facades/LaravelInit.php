<?php

namespace Fuelviews\Init\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fuelviews\Init\LaravelInit
 */
class LaravelInit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Fuelviews\Init\LaravelInit::class;
    }
}
