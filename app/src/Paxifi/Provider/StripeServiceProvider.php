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
        $this->registerConfiguration();

        $this->registerRoutes();
    }

    /**
     * Register routes for stripe.
     */
    public function registerRoutes() {
        $this->app['router']->group(['before' => 'oauth'], function () {
            $this->app['router']->group(['before' => 'oauth-owner:user'], function () {
                $this->app['router']->delete('me/stripe/disconnect', 'Paxifi\Stripe\Controller\StripeController@deauthorize');
                $this->app['router']->post('me/stripe/refund', 'Paxifi\Stripe\Controller\StripeController@refund');
            });
        });

        $this->app['router']->get('stripe/connect', 'Paxifi\Stripe\Controller\StripeController@authorize');

        $this->app['router']->post('stripe/charge', 'Paxifi\Stripe\Controller\StripeController@charge');

        $this->app['router']->get('stripe/redirect', 'Paxifi\Stripe\Controller\StripeController@redirect');
    }

    /**
     * Register stripe configurations.
     */
    public function registerConfiguration() {
        $this->app['config']->set('stripe.secret.key', getenv('STRIPE_SECRET_KEY'));

        $this->app['config']->set('stripe.publishable.key', getenv('STRIPE_PUBLISHABLE_KEY'));

        $this->app['config']->set('stripe.client.id', getenv('STRIPE_CLIENT_ID'));

        $this->app['config']->set('stripe.connect.api', getenv('STRIPE_CONNECT_API'));

        $this->app['config']->set('stripe.live.mode', getenv('STRIPE_LIVE_MODE'));

        $this->app['config']->set('stripe.application.fee.rate', getenv('STRIPE_APPLICATION_FEE_RATE'));

        $this->app['config']->set('stripe.redirect.url', getenv('STRIPE_REDIRECT_URL'));
    }
}