<?php

namespace Paxifi\Notification\Repository\Notifications;

use Paxifi\Notification\Repository\EloquentNotificationRepository;

class EmailNotification extends EloquentNotificationRepository
{
    protected $type = "email";
}