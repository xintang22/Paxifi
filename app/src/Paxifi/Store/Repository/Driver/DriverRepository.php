<?php namespace Paxifi\Store\Repository\Driver;

use Illuminate\Support\Facades\Facade;

class DriverRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Paxifi\Store\Repository\Driver\DriverRepositoryInterface';
    }
} 