<?php namespace Paxifi\Store\Auth;

use Illuminate\Support\Facades\Password as IlluminatePasswordFacade;

class Password extends IlluminatePasswordFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'driver.auth.reminder';
    }
} 