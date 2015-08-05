<?php namespace Paxifi\PushNotifications\Event;

use Paxifi\Traits\NotificationHelpersTrait;
use Log;

class PushNotificationsEventHandler
{

    use NotificationHelpersTrait;

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen('paxifi.push.notifications.payment.created', 'Paxifi\PushNotifications\Event\PushNotificationsEventHandler@onPaymentCreated');
        $events->listen('paxifi.push.notifications.payment.confirmed', 'Paxifi\PushNotifications\Event\PushNotificationsEventHandler@onPaymentConfirmed');
    }

    /**
     * @param $driver
     * @param $payment
     * @param $payment_method
     */
    public function onPaymentCreated($driver, $payment, $payment_method)
    {
        $options = [
            'custom' => [
                "driver_id" => $driver->id,
                "payment_id" => $payment->id,
                "payment_method" =>  $payment_method
            ]
        ];

        $parameters = [
            "currency" => $driver->currency,
            "amount" => $payment->order->total_sales
        ];

        switch($payment_method) {
            case 'cash':
                Log::info('Cash payment created', ['payment_id' => $payment->id]);
                $this->sendPushNotification($driver, 'cash_payment.created', $parameters, $options);
                break;
            case 'stripe':
                // Do nothing with stripe payment created.
                break;
        }
    }

    /**
     * @param $driver
     * @param $payment
     * @param $payment_method
     */
    public function onPaymentConfirmed($driver, $payment, $payment_method)
    {
        $options = [
            'custom' => [
                "driver_id" => $driver->id,
                "payment_id" => $payment->id,
                "payment_method" =>  $payment_method
            ]
        ];

        $parameters = [
            "currency" => $driver->currency,
            "amount" => $payment->order->total_sales
        ];

        switch($payment_method) {
            case 'cash':
                // Do nothing with cash payment confirmed
                break;
            case 'stripe':
                $this->sendPushNotification($driver, 'stripe_payment.confirmed', $parameters, $options);
                break;
        }
    }
} 