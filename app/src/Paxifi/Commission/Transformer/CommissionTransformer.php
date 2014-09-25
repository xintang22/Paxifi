<?php

namespace Paxifi\Commission\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Commission\Repository\EloquentCommissionRepository;

class CommissionTransformer extends TransformerAbstract {

    public function transform(EloquentCommissionRepository $commission) {
        return [
            'id' => $commission->id,
            'driver_id' => $commission->driver_id,
            'status' => $commission->status,
            'total_commission' => $commission->total_commission
        ];
}
} 