<?php

namespace Paxifi\Notification\Repository\Notifications;

use Paxifi\Notification\Repository\EloquentNotificationRepository;

class InventoryNotification extends EloquentNotificationRepository
{
    protected $type = "inventory";
} 