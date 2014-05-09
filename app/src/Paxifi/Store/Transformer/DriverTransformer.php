<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;

class DriverTransformer extends TransformerAbstract {

    public function transform($data)
    {
        return $data;
    }
} 