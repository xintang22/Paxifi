<?php namespace Paxifi\Provider;

use Illuminate\Auth\Reminders\PasswordBroker;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
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

        $this->registerRoutes();

        $this->registerDriverRepository();

        $this->registerAuthManager();

        $this->registerPasswordBroker();
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
            throw new StoreNotFoundException('Store does not exist.');
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
        // Search
        $this->app['router']->get('drivers/search', 'Paxifi\Store\Controller\DriverController@search');

        // CRUD
        $this->app['router']->get('drivers', 'Paxifi\Store\Controller\DriverController@index');
        $this->app['router']->post('drivers', 'Paxifi\Store\Controller\DriverController@store');
        $this->app['router']->get('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@show');
        $this->app['router']->put('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@update');
        $this->app['router']->delete('drivers/{driver}', 'Paxifi\Store\Controller\DriverController@destroy');

        // Subscriptions
        $this->app['router']->get('drivers/{driver}/subscriptions', 'Paxifi\Subscription\Controller\SubscriptionController@index');
        $this->app['router']->post('drivers/{driver}/subscriptions', 'Paxifi\Subscription\Controller\SubscriptionController@store');
        $this->app['router']->get('drivers/{driver}/subscriptions/{subscription}', 'Paxifi\Subscription\Controller\SubscriptionController@show');
        $this->app['router']->put('drivers/{driver}/subscriptions/{subscription}', 'Paxifi\Subscription\Controller\SubscriptionController@update');
        $this->app['router']->delete('drivers/{driver}/subscriptions/{subscription}', 'Paxifi\Subscription\Controller\SubscriptionController@cancel');

        // Products
        $this->app['router']->get('drivers/{driver}/products', 'Paxifi\Store\Controller\ProductController@index');
        $this->app['router']->post('drivers/{driver}/products', 'Paxifi\Store\Controller\ProductController@store');
        $this->app['router']->get('drivers/{driver}/products/{product}', 'Paxifi\Store\Controller\ProductController@show');
        $this->app['router']->put('drivers/{driver}/products/{product}', 'Paxifi\Store\Controller\ProductController@update');
        $this->app['router']->delete('drivers/{driver}/products/{product}', 'Paxifi\Store\Controller\ProductController@destroy');

        // sales
        $this->app['router']->get('drivers/{driver}/sales', 'Paxifi\Sales\Controller\SalesController@index');
        $this->app['router']->get('drivers/{driver}/sales/forecasts', 'Paxifi\Sales\Controller\SalesController@forecasts');

        // Authentication
        $this->app['router']->post('drivers/login', 'Paxifi\Store\Controller\AuthController@login');
        $this->app['router']->post('drivers/logout', 'Paxifi\Store\Controller\AuthController@logout');

        // Password reminder
        $this->app['router']->post('drivers/password/remind', 'Paxifi\Store\Controller\RemindersController@remind');
        $this->app['router']->get('drivers/password/reset/{token}', 'Paxifi\Store\Controller\RemindersController@show');
        $this->app['router']->post('drivers/password/reset', 'Paxifi\Store\Controller\RemindersController@reset');

        // Rating
        $this->app['router']->post('drivers/{driver}/rating', 'Paxifi\Store\Controller\RatingController@rating');
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
}