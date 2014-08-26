<?php namespace Paxifi\Paypal\Controller;

use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;

class PaypalController extends ApiController {

    /**
     * Subscribe the paypal account with driver account.
     */
    public function subscribe()
    {
        try {
            \DB::beginTransaction();

            $response = "";

            $listener = new Listener;
            $verifier = new CurlVerifier;
            $ipn = \Input::all();
            $ipnMessage = new Message($ipn);

            $verifier->setIpnMessage($ipnMessage);
            $verifier->setEnvironment(\Config::get('paxifi.paypal.environment'));

            $listener->setVerifier($verifier);

            $listener->listen(
                function() use ($listener, $ipn, &$response) {

                    // on verified IPN (everything is good!)
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    if ($driver = EloquentDriverRepository::find(\Input::get('custom'))) {

                        $response = \Event::fire('paxifi.paypal.subscription.' . $ipn['txn_type'], [$driver, $ipn]);

                    }
                },
                function() use ($listener) {

                    // on invalid IPN (somethings not right!)
                    $report = $listener->getReport();
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    return $this->setStatusCode(400)->respondWithError('Subscription failed.');
                }
            );

            $responseStatusCode = $response[0]->getStatusCode();
            $responseContent = $response[0]->getData();

            if ( $responseStatusCode >= 200 && $responseStatusCode <= 300 ) {
                \DB::commit();
            }

            return $this->setStatusCode($responseStatusCode)->respond($responseContent);
        } catch (\RuntimeException $e) {
            return $this->setStatusCode(400)->respondWithError($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }

    }


    public function store() {
//        try {
//            $subscribe = \Input::all();
//
//            if ($subscribe->business == \Config::get('paxifi.paypal.account')) {
//
//                if ($subscribe->payer_status == 'verified') {
//                    // Todo:: generate driver subscription object.
//
//
//                    // Todo:: update driver paypal account and subscribe satatus.
//                    $driver = EloquentDriverRepository::find(\Input::get('custom'));
//
//                    $driver->paypal_account = $subscribe->payer_email;
//                    $driver->status = 1;
//                    $driver->save();
//
//                    return $this->setStatusCode(200)->respond($driver);
//                }
//
//                return $this->setStatusCode(400)->errorWrongArgs('The subscription failed!');
//            }
//
//            return $this->setStatusCode(400)->errorWrongArgs('The subscription failed!');
//        } catch (\Exception $e) {
//
//        }
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