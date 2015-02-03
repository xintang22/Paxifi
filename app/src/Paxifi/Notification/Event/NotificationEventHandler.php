<?php

namespace Paxifi\Notification\Event;

use Paxifi\Notification\Repository\Notifications\BillingNotification;
use Paxifi\Notification\Repository\Notifications\EmailNotification;
use Paxifi\Notification\Repository\Notifications\InventoryNotification;
use Paxifi\Notification\Repository\Notifications\SalesNotification;
use Paxifi\Notification\Repository\Notifications\ThumbNotification;

class NotificationEventHandler
{

    /**
     * Find handler
     *
     * @param $data
     */
    public function handler($data)
    {
        call_user_func([__CLASS__, $data->type], $data);
    }

    /**
     * Thumbs notification event handler.
     *
     * @param $notification
     * @return bool
     */
    public function thumbs($notification)
    {
        $notification = [
            "driver_id" => $notification->driver_id,
            "value" => ($notification->feedback) ? "up" : "down",
            "type" => $notification->type
        ];

        if (ThumbNotification::create($notification))
        {
            return true;
        }

        return false;
    }

    /**
     * Sales notification event handler.
     *
     * @param $payment
     * @return bool
     */
    public function sales($payment)
    {
        $notification = [
            "driver_id" => $payment->order->OrderDriver()->id,
            "value" => $payment->id,
            "type" => $payment->type
        ];

        if (SalesNotification::create($notification))
        {
            return true;
        }

        return false;
    }

    /**
     * Product inventory event handler.
     *
     * @param $product
     * @return bool
     */
    public function inventory($product)
    {
        $notification = [
            "driver_id" => $product->driver->id,
            "value" => $product->id,
            "type" => $product->type
        ];

        if (InventoryNotification::create($notification))
        {
            return true;
        }

        return false;
    }

    /**
     * Create email notification.
     *
     * @param $driver
     * @return bool
     */
    public function emails($driver)
    {
        $notification = [
            "driver_id" => $driver->id,
            "value" => $driver->email,
            "type" => $driver->type
        ];

        if (EmailNotification::create($notification))
        {
            return true;
        }

        return false;
    }

    /**
     * Create billing notification.
     *
     * @param $commission
     * @return bool
     */
    public function billing($commission)
    {
        $notification = [
            "driver_id" => $commission->driver->id,
            "value" => $commission->commissions,
            "type" => $commission->type
        ];

        if (BillingNotification::create($notification))
        {
            return true;
        }

        return false;
    }

} 