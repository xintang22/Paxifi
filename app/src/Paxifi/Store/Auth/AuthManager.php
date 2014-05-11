<?php namespace Paxifi\Store\Auth;

use \Illuminate\Auth\AuthManager as IlluminateAuthManager;
use \Illuminate\Auth\DatabaseUserProvider;
use \Illuminate\Auth\Guard;
use \Illuminate\Auth\EloquentUserProvider;

class AuthManager extends IlluminateAuthManager
{
    /**
     * Create an instance of the database user provider.
     *
     * @return \Illuminate\Auth\DatabaseUserProvider
     */
    protected function createDatabaseProvider()
    {
        $connection = $this->app['db']->connection();

        $table = 'drivers';

        return new DatabaseUserProvider($connection, $this->app['hash'], $table);
    }

    /**
     * Create an instance of the Eloquent driver.
     *
     * @return \Illuminate\Auth\Guard
     */
    public function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();

        return new Guard($provider, $this->app['session.store']);
    }

    /**
     * Create an instance of the Eloquent user provider.
     *
     * @return \Illuminate\Auth\EloquentUserProvider
     */
    protected function createEloquentProvider()
    {
        $model = 'Paxifi\Store\Repository\EloquentDriverRepository';

        return new EloquentUserProvider($this->app['hash'], $model);
    }
} 