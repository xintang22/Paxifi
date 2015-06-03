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
        $payment_method = PaymentMethods::find($payment->payment_method_id);

        if ($payment_method->name == 'cash') {

            $payment->type = "sales";

            \Event::fire('paxifi.notifications.sales', [$payment]);
        }
    }
}

