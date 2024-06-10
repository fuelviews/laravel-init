<?php

namespace Fuelviews\LaravelInit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fuelviews\LaravelInit\LaravelInit
 */
class LaravelInit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Fuelviews\LaravelInit\LaravelInit::class;
    }
}
