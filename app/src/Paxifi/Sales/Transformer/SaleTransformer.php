<?php namespace Paxifi\Sales\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Order\Repository\EloquentOrderRepository;

class SaleTransformer extends TransformerAbstract
{
    public function transform($sale)
    {

        $order = EloquentOrderRepository::find($sale->order_id);

        return array(
            'id' => $order->id,
            'total_items' => $order->total_items,
            'total_costs' => $order->total_costs,
            'profit' => $order->profit,
            'commission' => $order->commission,
            'payment_method' => $order->payment_id,
            'payment_status' => '',
            'products' => $this->transformProducts($order),
            'buyer' => array(
                'email' => $order->buyer_email,
                'feedback' => $order->feedback,
                'comment' => $order->comment,
            ),
            'status' => $order->status,
            'created_at' => (string)$order->created_at,
            'updated_at' => (string)$order->updated_at,
        );
    }


    protected function transformProducts($order)
    {
        return $order->products()->get()->map(function ($product) {
            return array(
                'id' => $product->id,
                'quantity' => $product->pivot->quantity,
                'created_at' => (string)$product->pivot->created_at,
            );
        });

    }

} 