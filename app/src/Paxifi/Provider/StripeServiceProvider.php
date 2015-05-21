<?php
namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class StripeServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();
    }

    public function registerRoutes() {
        $this->app['router']->group(['before' => 'oauth'], function () {
            $this->app['router']->group(['before' => 'oauth-owner:user'], function () {
                $this->app['router']->post('me/stripe/conntect', 'Paxifi\Stripe\Controller\StripeController@authorize');
            });
        });
    }
}