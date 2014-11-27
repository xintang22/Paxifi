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
        $this->registerLogger();
    }

    /**
     * Register paypal module routes.
     */
    public function registerRoutes()
    {

        // Paypal ipn handler.
        $this->app['router']->post('paypal/payment', 'Paxifi\Paypal\Controller\PaypalController@payment');

        // Paypal subscribe.
        $this->app['router']->post('paypal/subscribe', 'Paxifi\Paypal\Controller\PaypalController@subscribe');

        $this->app['router']->group(['before' => 'oauth'], function () {

            $this->app['router']->group(['before' => 'oauth-owner:user'], function () {

                // Paypal user authorization.
                $this->app['router']->post('paypal/authorize', 'Paxifi\Paypal\Controller\PaypalController@authorize');

                // Paypal driver sales.
                $this->app['router']->post('paypal/commission', 'Paxifi\Commission\Controller\CommissionController@commission');

                // Paypal buy sticker single payment.
                $this->app['router']->post('paypal/sticker', 'Paxifi\Paypal\Controller\PaypalController@buySticker');

            });

        });
    }

    /**
     * Configure the paypal environment.
     */
    public function registerConfiguration()
    {
        $this->app['config']->set('paxifi.paypal.environment', getenv('PAYPAL_MODE'));

        // Paxifi paypal client
        $this->app['config']->set('paxifi.paypal.url', getenv('PAYPAL_URL'));
        $this->app['config']->set('paxifi.paypal.client_id', getenv('PAYPAL_CLIENT_ID'));
        $this->app['config']->set('paxifi.paypal.client_secret', getenv('PAYPAL_CLIENT_SECRET'));
    }

    /**
     * Register paypal subscription events.
     */
    public function registerEvents()
    {
        // fire driver subscribe event.
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_signup', 'Paxifi\Subscription\Controller\SubscriptionController@store');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_cancel', 'Paxifi\Subscription\Controller\SubscriptionController@cancel');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_modify', 'Paxifi\Subscription\Controller\SubscriptionController@modify');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_failed', 'Paxifi\Subscription\Controller\SubscriptionController@failed');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_payment', 'Paxifi\Subscription\Controller\SubscriptionController@subscribe');
        $this->app['events']->listen('paxifi.paypal.subscription.subscr_eot', 'Paxifi\Subscription\Controller\SubscriptionController@eot');

        // fire paypal payment event.
        $this->app['events']->listen('paxifi.paypal.payment.cart', 'Paxifi\Payment\Controller\PaymentController@paypalPaymentConfirmation');

        // paxifi.paypal.commission.payment
        $this->app['events']->listen('paxifi.paypal.commission.payment', 'Paxifi\Commission\Controller\CommissionController@commission');

        // paxifi.paypal.sticker.payment
        $this->app['events']->listen('paxifi.paypal.sticker.payment', 'Paxifi\Paypal\Controller\PaypalController@buySticker');
    }

    /**
     * Register a custom logger for paypal.
     */
    public function registerLogger()
    {
        $this->app->bindShared('paxifi.paypal.log', function ($app) {
            /** @var \Illuminate\Log\Writer $logger */
            $logger = $app->make('log');
            $logger->useFiles(storage_path().'/logs/paypal.log');

            return $logger;
        });
    }
}