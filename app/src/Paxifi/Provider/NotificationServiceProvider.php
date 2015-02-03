<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;
use Paxifi\Sticker\Exception\NotificationNotFoundException;
use Socket;

/**
 * Class NotificationServiceProvider
 * @package Paxifi\Provider
 */
class NotificationServiceProvider extends ServiceProvider
{

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

        $this->registerSocketChannels();
    }

    /**
     * Register notification routes.
     */
    public function registerRoutes()
    {
        $this->app['router']->group(['before' => 'oauth'], function () {

            // Get sales notifications.
            $this->app['router']->get('notifications', 'Paxifi\Notification\Controller\NotificationController@index');

            // Create notifications.
            $this->app['router']->post('notifications', 'Paxifi\Notification\Controller\NotificationController@store');
        });
    }

    /**
     * Register the events for notifications
     */
    public function registerEvents()
    {
        $this->app['events']->listen('paxifi.notifications.ranking', 'Paxifi\Notification\Event\NotificationEventHandler@handler');
        $this->app['events']->listen('paxifi.notifications.sales', 'Paxifi\Notification\Event\NotificationEventHandler@handler');
        $this->app['events']->listen('paxifi.notifications.stock', 'Paxifi\Notification\Event\NotificationEventHandler@handler');
        $this->app['events']->listen('paxifi.notifications.emails', 'Paxifi\Notification\Event\NotificationEventHandler@handler');
        $this->app['events']->listen('paxifi.notifications.billing', 'Paxifi\Notification\Event\NotificationEventHandler@handler');
        $this->app['events']->listen('paxifi.notifications.subscription', 'Paxifi\Notification\Event\NotificationEventHandler@handler');

        // Remove notification events
        $this->app['events']->listen('paxifi.notifications.sales.delete', 'Paxifi\Notification\Controller\NotificationController@cancelSales');
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
     * Register Socket Channels.
     *
     * @return void
     */
    protected function registerSocketChannels()
    {
        Socket::channel('notifications', 'Paxifi\Channels\NotificationsChannel');
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