<?php namespace Paxifi\Payment\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Payment\Repository\EloquentPaymentRepository;

class PaymentTransformer extends TransformerAbstract
{

    /**
     * Payment response transformer.
     *
     * @param EloquentPaymentRepository $payment
     *
     * @return array
     */
    public function transform(EloquentPaymentRepository $payment)
    {
        return [
            'id' => $payment->id,
            'total_items' => $payment->order->total_items,
            'total_costs' => $payment->order->total_costs,
            'total_sales' => $payment->order->total_sales,
            'total_tax' => $payment->order->total_tax,
            'order_id' => $payment->order_id,
            'order' => $this->embedOrder($payment),
            'status' => $payment->status,
            'feedback' => $payment->order->feedback,
            'comment' => $payment->order->comment,
            'updated_at' => $payment->updated_at,
        ];
    }

    /**
     * Embed order into payment response.
     *
     * @param $payment
     *
     * @return mixed
     */
    public function embedOrder($payment)
    {

        $order = $payment->order;

        $this->embedProducts($order);

        return $order;
    }

    /**
     * Embed products into payment response.
     *
     * @param $order
     *
     * @return array
     */
    public function embedProducts($order)
    {
        $products = [];

        $order->products->map(function ($product, $index) use (&$products) {
            $products[$index] = $product;
            $products[$index]['tax'] = array(
                'amount' => $product->tax_amount,
                'included_in_price' => (boolean)$product->driver->tax_included_in_price,
            );
        });

        return $products;
    }
} 