<?php namespace Paxifi\Order\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Order\Repository\OrderRepositoryInterface;
use Paxifi\Store\Repository\Product\ProductRepositoryInterface;
use Paxifi\Store\Transformer\ProductTransformer;

class OrderTransformer extends TransformerAbstract
{
    /**
     * @param OrderRepositoryInterface $order
     *
     * @return array
     */
    public function transform(OrderRepositoryInterface $order)
    {
        return array(
            'id' => $order->id,
            'total_costs' => $order->getTotalCosts(),
            'total_sales' => $order->getTotalSales(),
            'total_tax' => $order->getTotalTax(),
            'products' => $this->embedProducts($order)
        );
    }

    /**
     * Embed products into order response
     *
     * @param $order
     *
     * @return array
     */
    public function embedProducts($order) {
        $products = [];

        $order->products->map(function($product, $index) use(&$products) {
            $products[$index] = $product;
            $products[$index]['tax'] = array(
                'amount' => $product->tax_amount,
                'included_in_price' => (boolean)$product->driver->tax_included_in_price,
            );
        });

        return $products;
    }
}