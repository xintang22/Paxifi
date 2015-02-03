<?php

namespace Paxifi\Notification\Repository\Notifications;

use Paxifi\Notification\Repository\EloquentNotificationRepository;

class ThumbNotification extends EloquentNotificationRepository
{
    protected $type = "thumbs";
}