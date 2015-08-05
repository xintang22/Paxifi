<?php namespace Paxifi\PushNotifications;

use Illuminate\Support\ServiceProvider;
use Sly\NotificationPusher\Adapter\Apns;
use Sly\NotificationPusher\Adapter\Gcm;
use Sly\NotificationPusher\PushManager;

class PushNotificationsServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();

        $this->registerEvents();
    }

    /**
     * Register binding to laravel container.
     *
     * @return void.
     */
    private function registerBindings()
    {
        $this->app->bindShared('Sly\NotificationPusher\PushManager', function ($app) {
            $pushManager = new PushManager($app['config']->get('services.notifications.environment'));

            return $pushManager;
        });

        $this->app->bindShared('Sly\NotificationPusher\Adapter\Apns', function ($app) {
            $apns = new Apns([
                'certificate' => $app['config']->get('services.notifications.apns.certificate'),
                'passPhrase' => $app['config']->get('services.notifications.apns.passphrase'),
            ]);

            return $apns;
        });

        $this->app->bindShared('Sly\NotificationPusher\Adapter\Gcm', function ($app) {
            $apns = new Gcm([
                'apiKey' => $app['config']->get('services.notifications.gcm.apiKey'),
            ]);

            return $apns;
        });
    }

    private function registerEvents() {
        $this->app['events']->subscribe('Paxifi\PushNotifications\Event\PushNotificationsEventHandler');
    }
}