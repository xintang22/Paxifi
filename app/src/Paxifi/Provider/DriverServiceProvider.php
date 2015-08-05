<?php namespace Paxifi\Provider;

use Illuminate\Auth\Reminders\PasswordBroker;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Paxifi\Middleware\Subscription;
use Paxifi\Store\Auth\AuthManager;
use Paxifi\Store\Exception\StoreNotFoundException;
use Paxifi\Subscription\Exception\SubscriptionNotFoundException;

class DriverServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouteModelBindings();

        $this->registerDriverConfiguration();

        $this->registerRoutes();

        $this->registerDriverRepository();

        $this->registerAuthManager();

        $this->registerPasswordBroker();

        $this->registerEvents();

//        $this->registerMiddleWare();
    }

    /**
     * Register the driver configuration.
     */
    protected function registerDriverConfiguration()
    {
        $this->app['config']->set('images.drivers.logo', 'uploads/');
        $this->app['config']->set('images.drivers.template', 'images/drivers/template/');
        $this->app['config']->set('images.drivers.defaultlogo', 'driver_logo.png');
        $this->app['config']->set('paxifi.paypal.business', 'paxifiapp@gmail.com');
//        $this->app['config']->set('paxifi.paypal.business', '334531994-facilitator@qq.com');
    }

    /**
     * Register the driver repository implementation.
     *
     * @return void
     */
    protected function registerDriverRepository()
    {
        $this->app->bind('paxifi.repository.driver', 'Paxifi\Store\Repository\Driver\EloquentDriverRepository', true);
    }

    /**
     * Register model binding.
     *
     * @return void
     */
    protected function registerRouteModelBindings()
    {
        // Store
        $this->app['router']->model('driver', 'Paxifi\Store\Repository\Driver\EloquentDriverRepository', function () {
            // throw new StoreNotFoundException('Store does not exist.');
        });

        $this->app->error(function (StoreNotFoundException $exception) {
            return Response::json(array('error' => array(
                'context' => null,
                'message' => $exception->getMessage(),
                'code' => 404,
            )), 404);
        });

        // Subscription
        $this->app['router']->model('subscription', 'Paxifi\Subscription\Repository\EloquentSubscriptionRepository', function () {
            throw new SubscriptionNotFoundException('Subscription does not exist.');
        });

        $this->app->error(function (SubscriptionNotFoundException $exception) {
            return Response::json(array('error' => array(
                'context' => null,
                'message' => $exception->getMessage(),
                'code' => 404,
            )), 404);
        });
    }

    /**
     * Register Driver resource Routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        // driver has to be a numeric value
        $this->app['router']->pattern('driver', '[0-9]+');

        // @TODO Update to send directly temporary password
        $this->app['router']->get('drivers/password/reset/{token}', 'Paxifi\Store\Controller\RemindersController@show');
        $this->app['router']->post('drivers/password/reset', 'Paxifi\Store\Controller\RemindersController@reset');
        $this->app['router']->post('email/validate', 'Paxifi\Store\Controller\DriverController@emailValidate');

        $this->app['router']->group(['before' => 'oauth'], function () {

            // =========================================================================================================
            // OAuth
            // =========================================================================================================

            // Search
            $this->app['router']->get('drivers/search', 'Paxifi\Store\Controller\DriverController@search');
            $this->app['router']->get('stores/search', 'Paxifi\Store\Controller\DriverController@search');

            // Password reminder
            $this->app['router']->post('drivers/password/remind', 'Paxifi\Store\Controller\RemindersController@remind');

            // View Store's products
            $this->app['router']->get('drivers/{driver}/products', 'Paxifi\Store\Controller\ProductController@index');
            $this->app['router']->get('stores/{driver}/products', 'Paxifi\Store\Controller\ProductController@index');

            // Get driver's comments
            $this->app['router']->get('drivers/{driver}/comments', 'Paxifi\Feedback\Controller\FeedbackController@comments');
            $this->app['router']->get('stores/{driver}/comments', 'Paxifi\Feedback\Controller\FeedbackController@comments');

            $this->app['router']->get('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@show');
            $this->app['router']->get('stores/{driver}', 'Paxifi\Store\Controller\DriverController@show');
            // =========================================================================================================
            // OAuth + Client
            // =========================================================================================================

            $this->app['router']->group(['before' => 'oauth-owner:client'], function () {

                // Login the driver
                $this->app['router']->post('drivers/login', 'Paxifi\Store\Controller\AuthController@login');

                // Register new Store
                $this->app['router']->post('drivers', 'Paxifi\Store\Controller\DriverController@store');
                $this->app['router']->post('drivers/register', 'Paxifi\Store\Controller\DriverController@register');

                // Rating
                $this->app['router']->post('drivers/{driver}/rating', 'Paxifi\Store\Controller\RatingController@rating');
            });

            // =========================================================================================================
            // OAuth + Store owner
            // =========================================================================================================

            $this->app['router']->group(['before' => 'check-store-owner'], function () {

                // CRUD
//                $this->app['router']->get('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@show');
                $this->app['router']->put('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@update');
                $this->app['router']->delete('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@destroy');

                // Settings
                $this->app['router']->get('drivers/{driver}/settings', 'Paxifi\Store\Controller\DriverController@settings');
                $this->app['router']->put('drivers/{driver}/settings', 'Paxifi\Store\Controller\DriverController@updateSettings');

                // Products
                $this->app['router']->post('drivers/{driver}/products', 'Paxifi\Store\Controller\ProductController@store');
                $this->app['router']->get('drivers/{driver}/products/{product}', 'Paxifi\Store\Controller\ProductController@show');
                $this->app['router']->put('drivers/{driver}/products/{product}', 'Paxifi\Store\Controller\ProductController@update');
                $this->app['router']->delete('drivers/{driver}/products/{product}', 'Paxifi\Store\Controller\ProductController@destroy');

                // sales
                $this->app['router']->get('drivers/{driver}/sales', 'Paxifi\Sales\Controller\SalesController@index');
                $this->app['router']->get('drivers/{driver}/sales/forecasts', 'Paxifi\Sales\Controller\SalesController@forecasts');

                // taxes
                $this->app['router']->get('drivers/{driver}/taxes', 'Paxifi\Store\Controller\TaxController@index');

                // Logout the driver
                $this->app['router']->post('drivers/logout', 'Paxifi\Store\Controller\AuthController@logout');

                // @TODO Subscriptions
                $this->app['router']->get('drivers/{driver}/subscriptions', 'Paxifi\Subscription\Controller\SubscriptionController@index');
                $this->app['router']->post('drivers/{driver}/subscriptions', 'Paxifi\Subscription\Controller\SubscriptionController@store');
                $this->app['router']->get('drivers/{driver}/subscriptions/{subscription}', 'Paxifi\Subscription\Controller\SubscriptionController@show');
                $this->app['router']->put('drivers/{driver}/subscriptions/{subscription}', 'Paxifi\Subscription\Controller\SubscriptionController@update');
                $this->app['router']->delete('drivers/{driver}/subscriptions/{subscription}', 'Paxifi\Subscription\Controller\SubscriptionController@cancel');

                // Payment
                $this->app['router']->get('drivers/{driver}/payments/{payment}', 'Paxifi\Payment\Controller\PaymentController@meShow');
            });

            $this->app['router']->group(['before' => 'oauth-owner:user'], function () {

                // CRUD
                $this->app['router']->get('me', 'Paxifi\Store\Controller\DriverController@show');
                $this->app['router']->put('me', 'Paxifi\Store\Controller\DriverController@update');
                $this->app['router']->put('me/seller', 'Paxifi\Store\Controller\DriverController@seller');
                $this->app['router']->delete('me', 'Paxifi\Store\Controller\DriverController@destroy');

                // Settings
                $this->app['router']->get('me/settings', 'Paxifi\Store\Controller\DriverController@settings');
                $this->app['router']->put('me/settings', 'Paxifi\Store\Controller\DriverController@updateSettings');

                // Products
                $this->app['router']->post('me/products', 'Paxifi\Store\Controller\ProductController@store');
                $this->app['router']->get('me/products', 'Paxifi\Store\Controller\ProductController@index');
                $this->app['router']->get('me/products/{product}', 'Paxifi\Store\Controller\ProductController@showMine');
                $this->app['router']->put('me/products/weights', 'Paxifi\Store\Controller\ProductController@setWeight');
                $this->app['router']->put('me/products/{product}', 'Paxifi\Store\Controller\ProductController@updateMine');
                $this->app['router']->delete('me/products/{product}', 'Paxifi\Store\Controller\ProductController@destroyMine');
                $this->app['router']->get('me/count/products', 'Paxifi\Store\Controller\ProductController@getProductsCount');

                // sales
                $this->app['router']->get('me/sales', 'Paxifi\Sales\Controller\SalesController@index');
                $this->app['router']->get('me/sales/histories', 'Paxifi\Sales\Controller\SalesController@histories');
                $this->app['router']->post('me/sales/report', 'Paxifi\Sales\Controller\SalesController@report');
                $this->app['router']->get('me/sales/forecasts', 'Paxifi\Sales\Controller\SalesController@forecasts');

                // taxes
                $this->app['router']->get('me/taxes', 'Paxifi\Store\Controller\TaxController@index');

                // registerDeviceToken
                $this->app['router']->post('me/devices', 'Paxifi\PushNotifications\Controller\PushDeviceController@registerDeviceToken');

                // Logout the driver
                $this->app['router']->post('me/logout', 'Paxifi\Store\Controller\AuthController@logout');

                // Password change
                $this->app['router']->put('me/password/change', 'Paxifi\Store\Controller\DriverController@changePassword');

                // @TODO Subscriptions
                $this->app['router']->get('me/subscriptions', 'Paxifi\Subscription\Controller\SubscriptionController@index');
                $this->app['router']->put('me/subscriptions/renew', 'Paxifi\Store\Controller\DriverController@renewSubscription');
                $this->app['router']->put('me/subscriptions/cancel', 'Paxifi\Store\Controller\DriverController@cancelSubscription');
                $this->app['router']->put('me/subscriptions/reactive', 'Paxifi\Store\Controller\DriverController@reactiveSubscription');

                // Sticker
                $this->app['router']->get('me/sticker', 'Paxifi\Sticker\Controller\StickerController@show');
                $this->app['router']->post('me/sticker', 'Paxifi\Sticker\Controller\StickerController@store');
                $this->app['router']->put('me/sticker', 'Paxifi\Sticker\Controller\StickerController@update');
                $this->app['router']->post('me/sticker/email', 'Paxifi\Sticker\Controller\StickerController@email');
                $this->app['router']->post('me/sticker/shipment', 'Paxifi\Shipment\Controller\ShipmentController@shipment');

                // Notification
                $this->app['router']->get('me/notifications', 'Paxifi\Notification\Controller\NotificationController@show');
                $this->app['router']->delete('me/notifications', 'Paxifi\Notification\Controller\NotificationController@destroy');

                // Payment
                $this->app['router']->put('me/payments/{payment}', 'Paxifi\Payment\Controller\PaymentController@confirm');
            });

        });

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

    /**
     * Register the password broker instance.
     *
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->bindShared('driver.auth.reminder', function ($app) {
            // The reminder repository is responsible for storing the user e-mail addresses
            // and password reset tokens. It will be used to verify the tokens are valid
            // for the given e-mail addresses. We will resolve an implementation here.
            $reminders = $app['auth.reminder.repository'];

            $users = $app['driver.auth']->driver()->getProvider();

            $view = 'store.emails.auth.reminder';

            // The password broker uses the reminder repository to validate tokens and send
            // reminder e-mails, as well as validating that password reset process as an
            // aggregate service of sorts providing a convenient interface for resets.
            return new PasswordBroker(

                $reminders, $users, $app['mailer'], $view

            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.driver', 'driver.auth', 'driver.auth.reminder');
    }

    /**
     * Bind the events listener
     */
    public function registerEvents()
    {
        // fire email event.
        $this->app['events']->listen('paxifi.email', function ($emailOptions) {
            \Queue::push('Paxifi\Support\Queues\Queues@email', $emailOptions);
        });

        $this->app['events']->listen('paxifi.drivers.initialize', 'Paxifi\Store\Controller\DriverController@initialize');

        // fire driver logo generate event.
        $this->app['events']->listen(['paxifi.drivers.created', 'paxifi.store.photo.updated'], 'Paxifi\Store\Controller\DriverController@logo');

        // fire driver rating event.
        $this->app['events']->listen('paxifi.drivers.rating' , 'Paxifi\Store\Controller\RatingController@rating');

        // fire event to create driver sticker
        $this->app['events']->listen('paxifi.create.sticker', 'Paxifi\Sticker\Controller\StickerController@store');

        // fire driver seller_id  created event.
        $this->app['events']->listen('paxifi.email.sticker', 'Paxifi\Sticker\Event\EmailSticker@handle');

        // fire payment confirmed event.
        $this->app['events']->listen('paxifi.payment.confirmed', 'Paxifi\Store\EventsHandler@paymentConfirmed');

        // New driver registered
        $this->app['events']->subscribe('Paxifi\Store\Event\DriverEventHandler');
    }

    public function registerMiddleWare() {
        $this->app->middleware( new Subscription($this->app) );
    }
}