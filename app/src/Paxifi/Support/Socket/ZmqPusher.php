<?php namespace Paxifi\Support\Socket;

use Illuminate\Support\Facades\Config;

class ZmqPusher
{

    /**
     * @var \ZMQSocket
     */
    protected $socket;

    /**
     * The Constructor.
     */
    function __construct()
    {
        $this->socket = $this->connectToZmq();
    }

    /**
     * Connect to ZMQ Socket.
     *
     * @return mixed
     */
    protected function connectToZmq()
    {
        $host = Config::get('socket.zmqHost', 'localhost');

        $port = Config::get('socket.zmqPort', '5555');

        $persistentId = Config::get('socket.socketPushId', 'paxifi.zmq.push');

        $ctx = new \ZMQContext();

        $socket = $ctx->getSocket(\ZMQ::SOCKET_PUSH, $persistentId);

        $socket->connect("tcp://$host:$port");

        return $socket;
    }

    /**
     * Push to client.
     *
     * @param $channel
     * @param $data
     * @param array $to
     */
    public function push($channel, $data, $to = array())
    {
        $this->socket->send(json_encode(compact('channel', 'data', 'to')));
    }

} 