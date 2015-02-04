<?php namespace Paxifi\Shipment\Event;

use Paxifi\Shipment\Repository\EloquentShipmentRepository;

class ShipmentEventHandler
{

    public function newShipment($shipment)
    {
        $shipment['paypal_payment_status'] = 'completed';

        if ($shipment = EloquentShipmentRepository::create($shipment)) {
            return true;
        }
    }
} 