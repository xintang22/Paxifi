<?php namespace Paxifi\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Subscription implements HttpKernelInterface
{
    protected $app;

    /**
     * Create a new RateLimiter instance.
     *
     * @param  \Symfony\Component\HttpKernel\HttpKernelInterface  $app
     * @return void
     */
    public function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int     $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // Handle on passed down request
        $response = $this->app->handle($request, $type, $catch);

//        $requestsPerHour = 60;
//
//        // Rate limit by IP address
//        $key = sprintf('api:%s', $request->getClientIp());
//
//        // Add if doesn't exist
//        // Remember for 1 hour
//        \Cache::add($key, 0, 60);
//
//        // Add to count
//        $count = \Cache::increment($key);
//
//        if( $count > $requestsPerHour )
//        {
//            // Short-circuit response - we're ignoring
//            $response->setContent('Rate limit exceeded');
//            $response->setStatusCode(403);
//        }
//
//        $response->headers->set('X-Ratelimit-Limit', $requestsPerHour, false);
//        $response->headers->set('X-Ratelimit-Remaining', $requestsPerHour-(int)$count, false);

        return $response;
    }
}