<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;

class DriverTransformer extends TransformerAbstract {

    public function transform(DriverRepositoryInterface $driver)
    {
        return array(
            'email' => $driver->email,
            'photo' => $driver->photo,
            'name' => $driver->name,
            'address' => $driver->address,
            'currency' => $driver->currency,
        );
    }
} 