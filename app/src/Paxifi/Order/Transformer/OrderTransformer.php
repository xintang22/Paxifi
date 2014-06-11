<?php namespace Paxifi\Order\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Order\Repository\OrderRepositoryInterface;

class OrderTransformer extends TransformerAbstract
{
    public function transform(OrderRepositoryInterface $order)
    {
        return array(
            'id' => $order->id,
            'total_costs' => $order->getTotalCosts(),
            'total_sales' => $order->getTotalSales(),
        );
    }
}