<?php

namespace Paxifi\Notification\Repository\Notifications;

use Paxifi\Notification\Repository\EloquentNotificationRepository;

class BillingNotification extends EloquentNotificationRepository
{
    protected $type = "billing";
} 