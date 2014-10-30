<?php namespace Paxifi\Support\Socket;

use Illuminate\Container\Container;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class SocketServer implements WampServerInterface
{
    /**
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $channelHandlers;

    /**
     * @var \Paxifi\Support\Socket\ZmqPusher
     */
    protected $zmqPusher;

    /**
     * @var SocketPusher
     */
    protected $pusher;

    /**
     * The Constructor.
     *
     * @param Container $container
     * @param ZmqPusher $zmqPusher
     */
    function __construct(Container $container, ZmqPusher $zmqPusher)
    {
        $this->container = $container;

        $this->channelHandlers = new RouteCollection;

        $this->zmqPusher = $zmqPusher;

        $this->pusher = new SocketPusher;
    }

    /**
     * Register a new channel
     *
     * @param $pattern
     * @param $controller
     */
    public function channel($pattern, $controller)
    {
        $controllerInstance = $this->container->make($controller);

        $channelHandler = new ChannelEventHandler($pattern, array('_controller' => $controllerInstance));

        $this->channelHandlers->add($pattern, $channelHandler);
    }

    /**
     * Publish to clients.
     *
     * @param string $channel
     * @param array|mixed $data
     * @param array $to
     * @return void
     */
    public function pushFromServer($channel, $data, array $to = array())
    {
        $this->zmqPusher->push($channel, $data, $to);
    }

    /**
     * Handle the incoming messages from server, and broadcast them to relevant client.
     *
     * @param $message
     */
    public function onMessage($message)
    {
        $this->pusher->push($message);
    }

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @param  ConnectionInterface $connection The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $connection)
    {
        $this->pusher->addConnection($connection);
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $connection will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $connection The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $connection)
    {
        $this->pusher->removeConnection($connection);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param  ConnectionInterface $connection
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $connection, \Exception $e)
    {
        $connection->send(json_encode(['error']));
        $connection->close();
    }

    /**
     * An RPC call has been received
     *
     * @param \Ratchet\ConnectionInterface $connection
     * @param string $id The unique ID of the RPC, required to respond to
     * @param string|Topic $topic The topic to execute the call against
     * @param array $params Call parameters received from the client
     */
    function onCall(ConnectionInterface $connection, $id, $topic, array $params)
    {
        $connection->send(json_encode(['forbidden']));
        $connection->close();
    }

    /**
     * A request to subscribe to a topic has been made
     *
     * @param ConnectionInterface $connection
     * @param string|Topic $topic The topic to subscribe to
     * @internal param ConnectionInterface $connection
     */
    function onSubscribe(ConnectionInterface $connection, $topic)
    {
        $this->dispatch('subscribe', compact('connection', 'topic'));
    }

    /**
     * A request to unsubscribe from a topic has been made
     *
     * @param \Ratchet\ConnectionInterface $connection
     * @param string|Topic $topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $connection, $topic)
    {
        $this->dispatch('unsubscribe', compact('connection', 'topic'));
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     *
     * @param \Ratchet\ConnectionInterface $connection
     * @param string|Topic $topic The topic the user has attempted to publish to
     * @param string $event Payload of the publish
     * @param array $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param array $eligible A list of session Ids the message should be send to (whitelist)
     */
    function onPublish(ConnectionInterface $connection, $topic, $event, array $exclude, array $eligible)
    {
        $connection->send(json_encode(['forbidden']));
        $connection->close();
    }

    /**
     * @param $event
     * @param array $variables
     * @internal param ConnectionInterface $connection
     * @internal param $topic
     */
    protected function dispatch($event, array $variables = array())
    {
        try {
            $topic = $variables['topic'];

            $channel = $topic->getId();

            $context = new RequestContext($channel);

            $urlMatcher = new UrlMatcher($this->channelHandlers, $context);

            $params = $urlMatcher->match('/' . $channel);

            /** @var ChannelEventHandler $channelHandler */
            $channelHandler = $this->channelHandlers->get($params['_route']);

            $channelHandler->setWsParameters($variables);

            $channelHandler->setRequestParameters($params);

            $channelHandler->run($event);

            $this->cleanup($context, $urlMatcher, $params, $channelHandler);

        } catch (\Exception $e) {
        }
    }

    /**
     * Do some memory clean up. 
     *
     * @param $context
     * @param $urlMatcher
     * @param $params
     * @param $channelHandler
     */
    protected function cleanup($context, $urlMatcher, $params, $channelHandler)
    {
        unset($context);

        unset($urlMatcher);

        unset($params);

        unset($channelHandler);
    }
}