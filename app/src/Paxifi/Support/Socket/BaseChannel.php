<?php namespace Paxifi\Support\Socket;

use Illuminate\Support\Collection;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

class BaseChannel
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
        if (!isset($connection->channels)) {

            $connection->channels = new Collection;

        }

        $connection->channels->push($topic->getId());
    }

    /**
     * Handler to be executed after client un-subscription.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @return void
     */
    public function unsubscribe(ConnectionInterface $connection, Topic $topic)
    {
        $connection->channels->forget($topic->getId());
    }
}