<?php namespace Paxifi\Payment\Repository;

use Illuminate\Support\Facades\Facade;

class PaymentRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.payment';
    }
}