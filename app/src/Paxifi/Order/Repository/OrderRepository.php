<?php namespace Paxifi\Order\Repository;

use Illuminate\Support\Facades\Facade;

class OrderRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.order';
    }
}