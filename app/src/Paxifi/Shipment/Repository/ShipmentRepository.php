<?php namespace Paxifi\Shipment\Repository;

use Illuminate\Support\Facades\Facade;

class ShipmentRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.shipment';
    }
} 