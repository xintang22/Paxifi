<?php namespace Paxifi\Paypal\Controller;

use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;

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
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        // TODO: Implement getTransformer() method.
    }
}