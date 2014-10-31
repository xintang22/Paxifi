<?php namespace Paxifi\Support\Socket;

use Illuminate\Support\Collection;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

class SocketPusher
{
    /**
     * @var Collection
     */
    protected $connections;

    /**
     * The Constructor.
     */
    function __construct()
    {
        $this->connections = [];

        $this->auth = new SocketAuth;
    }

    /**
     * Add new connected client.
     *
     * @param ConnectionInterface $connection
     * @return void
     */
    public function addConnection(ConnectionInterface $connection)
    {

        if ($this->auth->authenticate($connection)) {

            $this->connections[$connection->userId][$connection->resourceId] = $connection;

        } else {

            $connection->send(json_encode([
                'error' => [
                    'type' => 'unauthorized',
                    'message' => 'Invalid access token',
                    'error_code' => 401,
                ]
            ]));

            $connection->close();

        }

    }

    /**
     * Remove a client connection.
     *
     * @param ConnectionInterface $connection
     * @return void
     */
    public function removeConnection(ConnectionInterface $connection)
    {
        if (isset($connection->userId)) {

            unset($this->connections[$connection->userId][$connection->resourceId]);

        }
    }

    /**
     * Push message to Clients.
     *
     * @param string $message
     * @return void
     */
    public function push($message)
    {
        $payload = json_decode($message);

        foreach ($payload->to as $userId) {

            if (isset($this->connections[$userId]) && $connections = $this->connections[$userId]) {

                foreach ($connections as $connection) {

                    $connection->event($payload->channel, $payload->data);

                }

            }
        }
    }

} 