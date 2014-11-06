<?php namespace Paxifi\Middleware;

use Carbon\Carbon;
use Paxifi\Commission\Repository\EloquentCommissionRepository;
use Paxifi\Sales\Repository\SaleCollection;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Subscription\Repository\EloquentPlanRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Subscription implements HttpKernelInterface
{
    protected $app;

    protected $paypal;

    protected $carbon;

    /**
     * Create a new RateLimiter instance.
     *
     * @param  \Symfony\Component\HttpKernel\HttpKernelInterface $app
     *
     * @return \Paxifi\Middleware\Subscription
     */
    public function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;

        $this->paypal = $this->app->make('Paxifi\Paypal\Paypal');

        $this->carbon = $this->app->make('Carbon\Carbon');
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

        $this->app->boot();

        if ($authorization = $request->headers->get('authorization')) {
            $token = $this->validateToken($authorization);

            // 1. Check owner_type
            if ($token['owner_type'] == 'user') {
                if ($driver = EloquentDriverRepository::findOrFail($token['owner_id'])) {

                    $subscription = $driver->subscription;

                    /**
                     * Need charge subscription fee.
                     */
                    if ($subscription->needChargeSubscription()) {
                        if ($subscriptionPayment = $this->paypal->subscriptionPayment(EloquentPlanRepository::findOrFail($subscription->plan_id), $driver)) {

                            $subscription->renewSubscription(EloquentPlanRepository::findOrFail($subscription->plan_id), $driver);

                        } else {

                            $subscription->expired();

                            // Todo:: record errors and send email to info user account expired.
                        }
                    }

                    /**
                     *
                     * Canceled status.
                     *
                     * Don't charge subscription, change driver status to 0, and change driver subscription to past_due.
                     *
                     */
                    if ($subscription->status == 'canceled') {

                        if (Carbon::now() >= $subscription->current_period_end) {

                            $subscription->expired();

                            // Todo:: send email info user account expired.

                        }
                    }
                }
            }
        }

        $response = $this->app->handle($request, $type, $catch);

        return $response;
    }

    /**
     * @param $authorization
     *
     * @return mixed
     */
    private function validateToken($authorization) {

        preg_match('/Bearer (.*)/', $authorization, $match);

        $token = trim($match[1]);

        $result = $this->app->make('League\OAuth2\Server\Storage\SessionInterface')->validateAccessToken($token);

        return $result;
    }
}