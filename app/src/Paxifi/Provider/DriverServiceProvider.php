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
     * Register Driver resource Routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->app['router']->get('drivers', 'Paxifi\Store\Controller\DriverController@index');
        $this->app['router']->post('drivers', 'Paxifi\Store\Controller\DriverController@store');

        // Authentication
        $this->app['router']->post('drivers/login', 'Paxifi\Store\Controller\AuthController@login');
        $this->app['router']->post('drivers/logout', 'Paxifi\Store\Controller\AuthController@logout');

        // Password reminder
        $this->app['router']->post('drivers/password/remind', 'Paxifi\Store\Controller\RemindersController@remind');
        $this->app['router']->get('drivers/password/reset/{token}', 'Paxifi\Store\Controller\RemindersController@show');
        $this->app['router']->post('drivers/password/reset', 'Paxifi\Store\Controller\RemindersController@reset');

        $this->app['router']->get('drivers/seller_id', 'Paxifi\Store\Controller\DriverController@checkSellerId');

        // Rating
        $this->app['router']->post('drivers/{id}/rating', 'Paxifi\Store\Controller\RatingController@rating');
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