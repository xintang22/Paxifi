<?php namespace Paxifi\Support\Socket;

use Illuminate\Support\Facades\Facade;

class Socket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'socket_server';
    }
} 