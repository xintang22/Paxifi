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

        if ($payment_method->name != 'paypal') {
            \Event::fire('paxifi.notifications.sales', [$payment]);
        }
    }

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  $payment
     */
//    public function updated($payment)
//    {
//        \Event::fire('paxifi.notifications.sales', [$payment]);
//    }

}

