<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
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

    public function registerRoutes()
    {
        $router = $this->app['router'];

        $router->group(['prefix' => 'app/settings', 'before' => 'oauth'], function () use ($router) {
            $router->get('countries', 'Paxifi\Settings\Controller\SettingsController@getCountries');
        });
    }
}