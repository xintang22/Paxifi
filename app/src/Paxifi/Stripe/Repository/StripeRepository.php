<?php namespace Paxifi\Stripe\Repository;

use Illuminate\Support\Facades\Facade;

class StripeRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.stripe';
    }
} 