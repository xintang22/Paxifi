<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class PaypalServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfiguration();
        $this->registerEvents();
        $this->registerRoutes();
    }

    public function registerRoutes()
    {

        // Paypal ipn handler.
        $this->app['router']->post('paypal/ipn', 'Paxifi\Payment\Controller\PaymentController@ipn');

        // Paypal subscribe
        $this->app['router']->post('paypal/subscribe', 'Paxifi\Paypal\Controller\PaypalController@subscribe');
    }

    public function registerConfiguration()
    {
        $this->app['config']->set('paxifi.environment', 'sandbox');
    }

    public function registerEvents()
    {
        // fire driver subscribe event.
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_signup', 'Paxifi\Subscription\Controller\SubscriptionController@store');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_cancel', 'Paxifi\Subscription\Controller\SubscriptionController@cancel');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_modify', 'Paxifi\Subscription\Controller\SubscriptionController@modify');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_failed', 'Paxifi\Subscription\Controller\SubscriptionController@failed');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_payment', 'Paxifi\Subscription\Controller\SubscriptionController@payment');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_eot', 'Paxifi\Subscription\Controller\SubscriptionController@eot');
    }
}