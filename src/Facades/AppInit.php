<?php

namespace Fuelviews\AppInit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fuelviews\AppInit\AppInit
 */
class AppInit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fuelviews\AppInit\AppInit::class;
    }
}
