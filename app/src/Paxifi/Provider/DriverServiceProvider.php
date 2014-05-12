<?php namespace Paxifi\Provider;

use Illuminate\Auth\Reminders\PasswordBroker;
use Illuminate\Support\ServiceProvider;
use Paxifi\Store\Auth\AuthManager;

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

        $this->registerAuthManager();
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
        $this->app['router']->post('drivers/login', 'Paxifi\Store\Controller\AuthController@login');
        $this->app['router']->post('drivers/logout', 'Paxifi\Store\Controller\AuthController@logout');
    }

    /**
     * Register the Auth manager.
     *
     * @return void
     */
    protected function registerAuthManager()
    {
        $this->app->bindShared('driver.auth', function ($app) {
            $app['auth.loaded'] = true;

            return new AuthManager($app);
        });
    }
}