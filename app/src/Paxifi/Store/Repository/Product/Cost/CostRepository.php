<?php namespace Paxifi\Store\Repository\Product\Cost;

use Illuminate\Support\Facades\Facade;

class CostRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.cost';
    }
}