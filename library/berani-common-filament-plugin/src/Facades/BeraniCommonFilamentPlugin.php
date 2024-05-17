<?php

namespace BeraniDigitalID\BeraniCommonFilamentPlugin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BeraniDigitalID\BeraniCommonFilamentPlugin\BeraniCommonFilamentPlugin
 */
class BeraniCommonFilamentPlugin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \BeraniDigitalID\BeraniCommonFilamentPlugin\BeraniCommonFilamentPlugin::class;
    }
}
