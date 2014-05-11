<?php namespace Paxifi\Store\Auth;

use Illuminate\Support\Facades\Facade;

/**
 * @see AuthManager
 * @see \Illuminate\Auth\Guard
 */
class Auth extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'driver.auth';
    }

}
