<?php

namespace Paxifi\Notification\Repository\Notifications;

use Paxifi\Notification\Repository\EloquentNotificationRepository;

class SalesNotification extends EloquentnotificationRepository
{
    protected $type = "sales";
}