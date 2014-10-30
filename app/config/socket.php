<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Socket Default Port
    |--------------------------------------------------------------------------
    */

    'socketPort' => 8080,

    /*
    |--------------------------------------------------------------------------
    | ZMQ Default Host
    |--------------------------------------------------------------------------
    */

    'zmqHost' => '127.0.0.1',

    /*
    |--------------------------------------------------------------------------
    | ZMQ Default Port
    |--------------------------------------------------------------------------
    */
    'zmqPort' => 5555,

    /*
    |--------------------------------------------------------------------------
    | ZMQ socket pull persistent id
    |--------------------------------------------------------------------------
    */

    'socketPullId' => 'paxifi.zmq.pull',

    /*
    |--------------------------------------------------------------------------
    | ZMQ socket push persistent id
    |--------------------------------------------------------------------------
    */

    'socketPushId' => 'paxifi.zmq.push',

];
