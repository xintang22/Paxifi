<?php namespace Paxifi\Channels;

use Carbon\Carbon;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Socket\BaseChannel;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

class NotificationsChannel extends BaseChannel
{
    /**
     * Handler to be executed after client subscription.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @return void
     */
    public function subscribe(ConnectionInterface $connection, Topic $topic)
    {

        parent::subscribe($connection, $topic);

        $driver = EloquentDriverRepository::find($connection->userId);

        $to = Carbon::createFromTimestamp(Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U'));

        $from = Carbon::createFromTimestamp(Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U') - (60 * 60 * \Config::get('notification_hours')));

        $notifications = $driver->with_notifications($from, $to);

        $connection->event($topic->getId(), $notifications);

    }
} 