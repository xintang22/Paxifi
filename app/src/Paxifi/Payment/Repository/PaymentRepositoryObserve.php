<?php namespace Paxifi\Payment\Repository;

use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository as PaymentMethods;

class PaymentRepositoryObserve {

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  $payment
     */
    public function created($payment)
    {
        $payment_method = PaymentMethods::find($payment->payment_method_id)->name;

        $driver = $payment->order->products->first()->driver()->first();

        if ($payment_method == 'cash') {

            $payment->type = "sales";

            \Event::fire('paxifi.notifications.sales', [$payment]);
        }

        \Event::fire('paxifi.push.notifications.payment.created', ["driver" => $driver, "payment" => $payment, "payment_method" => $payment_method]);
    }
}

