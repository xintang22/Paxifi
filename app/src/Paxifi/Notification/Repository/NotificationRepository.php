<?php namespace Paxifi\Notification\Repository;

use Illuminate\Support\Facades\Facade;

class NotificationRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.notifications';
    }
}