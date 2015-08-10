<?php namespace Paxifi\Traits;

use Config, App;
use Log;

trait NotificationHelpersTrait {

    /**
     * @var bool
     */
    protected $sendPushNotifications = true;

    /**
     * Send push notification to the Account
     *
     * @param $driver
     * @param $message
     * @param array $parameters
     * @param array $options
     */
    public function sendPushNotification($driver, $message, $parameters = [], $options = [])
    {
        // Don't send push notifications
        if (!$this->sendPushNotifications) {
            return;
        }

        $pusher = App::make('Paxifi\PushNotifications\NotificationPusher');

        // get account devices
        $devices = $this->getDriverPushDevices($driver);

        if (!empty($devices['ios'])) {
//            $pusher->pushApns($devices['ios'], trans('messages.push_notifications.' . $message, $parameters, 'messages', $account->language));
            Log::info('Pushed ios devices', ['devices' => $devices['ios']]);
            $pusher->pushApns($devices['ios'], trans('messages.push_notifications.' . $message, $parameters), $options);
        }

        if (!empty($devices['android'])) {
            $pusher->pushGcm($devices['android'], trans('messages.push_notifications.' . $message, $parameters), $options);
        }
    }

    /**
     * Get the account registered devices
     *
     * @param $driver
     * @return array
     */
    private function getDriverPushDevices($driver)
    {
        $devices = [
            'ios' => [],
            'android' => [],
        ];

        $driverDevices = $driver->push_devices()->get();

        if (!empty($driverDevices)) {
            $driverDevices->each(function ($device) use (&$devices) {
                if ($device->type == 'ios') {
                    $devices['ios'][] = $device->token;
                }
                elseif ($device->type == 'android') {
                    $devices['android'][] = $device->token;
                }
            });
        }

        return $devices;
    }

}