<?php namespace Paxifi\Paypal\Controller;

use Carbon\Carbon;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;
use Paxifi\Sales\Repository\SaleCollection;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Authorization;

class PaypalController extends ApiController
{

    /**
     * Paypal cart payment ipn handler
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment()
    {
        try {
            \DB::beginTransaction();

            $listener = new Listener;
            $verifier = new CurlVerifier;
            $ipn = \Input::all();
            $ipnMessage = new Message($ipn);

            $verifier->setIpnMessage($ipnMessage);
            $verifier->setEnvironment(\Config::get('paxifi.paypal.environment'));

            $listener->setVerifier($verifier);

            $listener->listen(
                function () use ($listener, $ipn, &$response) {

                    // on verified IPN (everything is good!)
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    // Find paid order (custom is order_id).
                    if ($order = EloquentOrderRepository::find(\Input::get('custom'))) {

                        /**
                         * Check the paypal payment.
                         *
                         * Total sales ==  ipn['mc_gross']
                         *
                         * Driver Paypal Account == ipn['business]
                         */
                        // Todo:: add payment verify method
                        if ($ipn['payment_status'] == 'Completed' &&
                            $order->total_sales == $ipn['payment_gross'] &&
                            $order->OrderDriver()->paypal_account == $ipn['business']
                        ) {
                            \Event::fire('paxifi.paypal.payment.' . $ipn['txn_type'], [$order->payment, $ipn]);
                        }

                        \DB::commit();
                    }

                    return $this->setStatusCode(404)->respondWithError('Order Not Found');
                },
                function () use ($listener) {

                    // on invalid IPN (somethings not right!)
                    $report = $listener->getReport();
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    return $this->setStatusCode(400)->respondWithError('Payment failed.');
                }
            );
        } catch (\RuntimeException $e) {
            return $this->setStatusCode(400)->respondWithError($e->getMessage());
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return $this->errorInternalError();
        }
    }

    /**
     * Paypal subscription ipn handler.
     */
    public function subscribe()
    {
//        $ipn = \Input::all();
//        \Log::useFiles(storage_path().'/logs/'. 'sub-'. time(). '.txt');
//        \Log::info($ipn);
//        die;
        try {
            \DB::beginTransaction();
            $listener = new Listener;
            $verifier = new CurlVerifier;
            $ipn = \Input::all();
            $ipnMessage = new Message($ipn);

            $verifier->setIpnMessage($ipnMessage);
            $verifier->setEnvironment(\Config::get('paxifi.paypal.environment'));

            $listener->setVerifier($verifier);

            $listener->listen(
                function () use ($listener, $ipn, &$response) {

                    // on verified IPN (everything is good!)
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    // Find paid driver (custom is driver_id).
                    if ($driver = EloquentDriverRepository::find(\Input::get('custom'))) {

                        \Event::fire('paxifi.paypal.subscription.' . $ipn['txn_type'], [$driver, $ipn]);

                        \DB::commit();

                    }
                },
                function () use ($listener) {

                    // on invalid IPN (somethings not right!)
                    $report = $listener->getReport();
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    return $this->setStatusCode(400)->respondWithError('Subscription failed.');
                }
            );
        } catch (\RuntimeException $e) {
            return $this->setStatusCode(400)->respondWithError($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }

    }

    /**
     * Handle the paypal authorization process.
     *
     * 1. get authorization code response
     * 2. process the code and store refresh token
     */
    public function authorize()
    {
        try {
            \DB::beginTransaction();
            if ($driver = $this->getAuthenticatedDriver()) {
                $authorizationCode = \Input::get('response.code');

                $url = \Config::get('paxifi.paypal.url');

                $client = new \GuzzleHttp\Client();

                $res = $client->post($url, [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'auth' => ['AWS54BAuSLHhRKWeYKLyah03y09dEtuu_haQHlBuu_XJgrgDjGzPkawZgcu_', 'EMt35xD7ksEW7RDrHp60SCOTExhRIsv38tujA6x-x8cjl4LGtsXu1YbE98qy'],
                    'body' => [
                        'grant_type' => 'authorization_code',
                        'response_type' => 'token',
                        'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
                        'code' => $authorizationCode
                    ]
                ]);

                if ($res->getStatusCode() == 200) {
                    $driver->paypal_refresh_token = $res->json()['refresh_token'];

                    $driver->save();

                    \DB::commit();

                    return $this->setStatusCode(200)->respond('success');
                }

                return $this->setStatusCode(400)->respondWithError('');
            }

        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    // Create commission paypal payment.
    public function commission($driver=null)
    {
        try {

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            // Get the pre-month commissions.
            $sales = new SaleCollection($driver->sales(Carbon::now()->subMonth(), Carbon::now()));

            // Todo::create paypal future payment.
            


        } catch(\Exception $e) {

        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        // TODO: Implement getTransformer() method.
    }
}