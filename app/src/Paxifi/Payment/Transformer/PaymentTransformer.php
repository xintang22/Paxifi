<?php namespace Paxifi\Payment\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Payment\Repository\EloquentPaymentRepository;

class PaymentTransformer extends TransformerAbstract {

    public function transform(EloquentPaymentRepository $payment)
    {
        return [

        ];
    }
} 