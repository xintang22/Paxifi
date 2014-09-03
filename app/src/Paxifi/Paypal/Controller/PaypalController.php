<?php namespace Paxifi\Paypal\Controller;

use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;

class PaypalController extends ApiController
{

    public function payment()
    {

        $ipn = \Input::all();

        \Log::useFiles(storage_path().'/logs/ipn-'.time().'.txt');

        \Log::info($ipn);
//        try {
//            $ipn = \Input::all();
//            if ($ipn['payer_status'] == 'verified') {
//                if ($payment = Payment::find($ipn['custom'])) {
//                    if (($payment->order->total_sales == $ipn['payment_gross']) && ($ipn['business'] == $payment->order->OrderDriver()->paypal_account)) {
//                        $payment->paypal_transaction_id = $ipn['txn_id'];
//                        $payment->paypal_transaction_status = 1;
//                        $payment->status = 1;
//                        $payment->save();
//
//                        return $this->setStatusCode(200)->respondWithItem($payment);
//                    }
//
//                    return $this->setStatusCode(400)->respondWithError('Payment is not success.');
//                }
//
//                return $this->setStatusCode(404)->respondWithError('The payment is not found.');
//            }
//        } catch (\Exception $e) {
//            return $this->errorInternalError();
//        }

    }

//
//    public function verifyPaypalPayment($payment) {
//        try {
//            if ($payment->paypal_transaction_status && $payment->status) {
//                return $this->setStatusCode(200)->respondWithItem($payment);
//            }
//        } catch(\Exception $e) {
//            return $this->errorInternalError();
//        }
//    }

    /**
     * Subscribe the paypal account with driver account.
     */
    public function subscribe()
    {
        try {
            \DB::transiction();
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

                    if ($driver = EloquentDriverRepository::find(\Input::get('custom'))) {
                        if (!$subscription = $driver->getActiveSubscription()) {
                            \Event::fire('paxifi.paypal.subscription.' . $ipn['txn_type'], [$driver, $ipn]);

                            \DB::commit();
                        }
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