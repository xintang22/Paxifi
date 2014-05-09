<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class DriverServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->registerDriverRepository();
    }

    /**
     * Register the driver repository implementation.
     *
     * @return void
     */
    protected function registerDriverRepository()
    {
        $this->app->bind('Paxifi\Store\Repository\DriverRepositoryInterface', 'Paxifi\Store\Repository\EloquentDriverRepository', true);
    }

    /**
     * Register Driver resource Routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->app['router']->get('drivers', 'Paxifi\Store\Controller\DriverController@index');
        $this->app['router']->post('drivers', 'Paxifi\Store\Controller\DriverController@store');
    }
}