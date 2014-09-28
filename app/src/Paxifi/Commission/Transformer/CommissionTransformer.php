<?php

namespace Paxifi\Commission\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Commission\Repository\EloquentCommissionRepository;

class CommissionTransformer extends TransformerAbstract
{

    public function transform(EloquentCommissionRepository $commission)
    {
        return [
            'id' => $commission->id,
            'driver_id' => $commission->driver_id,
            'status' => $commission->status,
            'commissions' => $commission->commissions,
            'capture_id' => $commission->capture_id,
            'capture_status' => $commission->capture_status,
            'capture_created_at' => $commission->capture_created_at,
            'capture_updated_at' => $commission->capture_updated_at,
        ];
    }
} 