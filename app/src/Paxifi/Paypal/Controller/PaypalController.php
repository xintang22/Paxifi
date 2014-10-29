<?php namespace Paxifi\Paypal\Controller;

use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Paypal\Helper\PaypalHelper;
use Paxifi\Shipment\Repository\EloquentShipmentRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;
use Paxifi\Sales\Repository\SaleCollection;

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
            \Log::useFiles(storage_path().'/logs/'. 'payment-'. time(). '.txt');
            \Log::info($ipn);
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

                $client = new \GuzzleHttp\Client();

                $res = $client->post(\Config::get('paxifi.paypal.url') . 'oauth2/token', [
                    'auth' => [\Config::get('paxifi.paypal.client_id'), \Config::get('paxifi.paypal.client_secret')],
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

                    return $this->setStatusCode(200)->respond(['authorize' => true]);
                }

                return $this->setStatusCode(400)->respondWithError('');
            }

        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Paypal payment for sticker.
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buySticker($driver = null)
    {
        try {
            \DB::beginTransaction();
            if (is_null($driver)) {
                $driver = $driver = $this->getAuthenticatedDriver();
            }

            $paypal_helper = new PaypalHelper($driver);

            $sticker_payment = \Input::all();

            if ($payment = $paypal_helper->verifyPaypalSinglePayment($sticker_payment)) {
                $shipment = EloquentShipmentRepository::find(\Input::get('shipment_id'));

                $shipment->payment_status = 'completed';

                $shipment->save();
                \DB::commit();
            }

        } catch (\Exception $e) {
            return $this->setStatusCode(400)->respondWithError($e->getMessage());
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