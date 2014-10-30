<?php namespace Paxifi\Support\Socket;

use Illuminate\Console\Command;
use App;
use Config;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;
use React\ZMQ\Context;

class ListenCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'paxifi:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start listening on specified port for incoming connections';

    /**
     * The socket server handler
     *
     * @var \Paxifi\Support\Socket\SocketServer
     */
    protected $socketServer;

    /**
     * Create a new command instance.
     *
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();

        $this->socketServer = App::make('socket_server');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // Create React event loop
        $loop = \React\EventLoop\Factory::create();

        $this->setupZmqPuller($loop);

        $this->setupWebSocketServer($loop);

        $loop->run();
    }

    /**
     * Connect to ZMQ socket and listen to incoming message
     *
     * @param $loop
     */
    protected function setupZmqPuller($loop)
    {
        $host = Config::get('socket.zmqHost', 'localhost');

        $port = Config::get('socket.zmqPort', '5555');

        $persistentId = Config::get('socket.socketPullId', 'paxifi.zmq.pull');

        $context = new Context($loop);

        $pull = $context->getSocket(\ZMQ::SOCKET_PULL, $persistentId);

        $pull->bind("tcp://$host:$port");

        $pull->on('message', [$this->socketServer, 'onMessage']);

        $this->info(sprintf('Server listening to incoming messages from ZMQ at %s:%s', $host, $port));
    }

    /**
     * Setup the web socket server.
     *
     * @param $loop
     * @throws \React\Socket\ConnectionException
     */
    protected function setupWebSocketServer($loop)
    {
        $webSock = new Server($loop);

        $webSock->listen(Config::get('socket.socketPort', 8080), '0.0.0.0');

        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer(
                        $this->socketServer
                    )
                )
            ),
            $webSock
        );

        $this->info(sprintf('Server listening at 0.0.0.0:%s', Config::get('socket.socketPort', 8080)));
    }

}
