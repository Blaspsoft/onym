<?php

namespace Blaspsoft\Onym\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Blaspsoft\Onym\Skeleton\SkeletonClass
 */
class Onym extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'onym';
    }
}
