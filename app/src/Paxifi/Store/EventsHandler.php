<?php namespace Paxifi\Store;

use Paxifi\Store\Repository\Product\EloquentProductRepository;

class EventsHandler {
    /**
     * After payment confirmed, this method will be executed.
     *
     * @param $payment
     */
    function paymentConfirmed($payment) {
        $products = $payment->order->products;

        $products->map(function ($product) {
            // Fires an event to update the inventory.
            \Event::fire('paxifi.product.ordered', array($product, $product['pivot']['quantity']));

            // Fires an event to notification the driver that the product is in low inventory.
            if (EloquentProductRepository::find($product->id)->inventory <= 5) {

                $product->type = "inventory";

                \Event::fire('paxifi.notifications.stock', [$product]);
            }
        });
    }
} 