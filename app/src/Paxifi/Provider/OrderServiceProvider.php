<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();
    }

    /**
     * Register Order resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->post('orders', 'Paxifi\Order\Controller\OrderController@store');
    }
}