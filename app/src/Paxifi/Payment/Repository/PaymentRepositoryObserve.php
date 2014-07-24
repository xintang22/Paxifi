<?php namespace Paxifi\Payment\Repository;

use Paxifi\Payment\Repository\EloquentPaymentRepository as Payment;

class PaymentRepositoryObserve {

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  $payment
     */
    public function created($payment)
    {
        \Event::fire('paxifi.notifications.sales', [$payment]);
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

