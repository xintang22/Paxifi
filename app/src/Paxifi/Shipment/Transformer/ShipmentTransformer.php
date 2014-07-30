<?php namespace Paxifi\Shipment\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Shipment\Repository\EloquentShipmentRepository;

class ShipmentTransformer extends TransformerAbstract {

    public function transform(EloquentShipmentRepository $shipment)
    {
        return [
            'id' => $shipment->id,
            'status' => $shipment->status,
            'address' => $shipment->address
        ];
    }
}