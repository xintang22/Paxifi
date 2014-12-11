<?php
/**
 * Created by PhpStorm.
 * User: Sonny
 * Date: 12/10/14
 * Time: 7:00 PM
 */

namespace Paxifi\Notification\Repository\Notifications;


use Paxifi\Notification\Repository\EloquentNotificationRepository;

class SubscriptionNotification extends EloquentNotificationRepository
{
    protected $type = "subscription";
} 