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

        // TODO:: fire event to publish notifications for payment confirmed.
        $driver = $payment->order->products->first()->driver()->first();

        $payment_method = $payment->payment_method()->first()->name;

        \Event::fire('paxifi.push.notifications.payment.confirmed', ["driver" => $driver, "payment" => $payment, "payment_method" => $payment_method]);
    }
} 