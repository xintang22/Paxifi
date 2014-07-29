<?php namespace Paxifi\Payment\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Payment\Repository\EloquentPaymentRepository;

class PaymentTransformer extends TransformerAbstract {

    public function transform(EloquentPaymentRepository $payment)
    {
        return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'status' => $payment->status,
            'feedback' => $payment->order->feedback,
            'comment' => $payment->order->comment,
            'updated_at' => $payment->updated_at,
        ];
    }
} 