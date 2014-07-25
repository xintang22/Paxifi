<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;
use Paxifi\Sticker\Exception\NotificationNotFoundException;

/**
 * Class NotificationServiceProvider
 * @package Paxifi\Provider
 */
class NotificationServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->registerEvents();

        $this->registerNotificationRepository();
    }

    /**
     * Register notification routes.
     */
    public function registerRoutes()
    {
        $this->app['router']->group(['before' => 'oauth'], function () {
            $this->app['router']->post('notifications', 'Paxifi\Notification\Controller\NotificationController@store');
        });
    }

    /**
     * Register the events for notifications
     */
    public function registerEvents()
    {
        $this->app['events']->listen('paxifi.notifications.billing', 'Paxifi\Notification\Controller\NotificationController@billing');
        $this->app['events']->listen('paxifi.notifications.sales', 'Paxifi\Notification\Controller\NotificationController@sales');
        $this->app['events']->listen('paxifi.notifications.stock', 'Paxifi\Notification\Controller\NotificationController@stock');
        $this->app['events']->listen('paxifi.notifications.ranking', 'Paxifi\Notification\Controller\NotificationController@ranking');
        $this->app['events']->listen('paxifi.notifications.emails', 'Paxifi\Notification\Controller\NotificationController@emails');
    }

    /**
     * Register the notification repository implementation.
     *
     * @return void
     */
    protected function registerNotificationRepository()
    {
        $this->app->bind('paxifi.repository.notifications', 'Paxifi\Notification\Repository\EloquentNotificationRepository', true);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.notifications');
    }

    /**
     * Setting up the notification configuration for notification keep hours.
     */
    public function boot()
    {
        parent:: boot();

        $this->app['config']->set('notification_hours', 72);
    }
}