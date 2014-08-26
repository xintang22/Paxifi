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
            $listener = new Listener;
            $verifier = new CurlVerifier;
            $ipn = \Input::all();
            $ipnMessage = new Message($ipn);

            $verifier->setIpnMessage($ipnMessage);
            $verifier->setEnvironment(\Config::get('paxifi.environment'));

            $listener->setVerifier($verifier);

            $listener->listen(
                function() use ($listener, $ipn) {
                    // on verified IPN (everything is good!)
                    $resp = $listener->getVerifier()->getVerificationResponse();

                    if ($driver = EloquentDriverRepository::find(\Input::get('custom'))) {
                        \Event::fire('paxifi.paypal.subscription.' . $ipn['txn_type'], [$driver, $ipn]);
                    }

                    \DB::commit();

                },
                function() use ($listener) {

                    // on invalid IPN (somethings not right!)
                    $report = $listener->getReport();
                    $resp = $listener->getVerifier()->getVerificationResponse();

                }
            );
        } catch (\RuntimeException $e) {
            return $this->setStatusCode(400)->respondWithError($e->getMessage());
        } catch (\Exception $e) {
            return $e->getMessage();
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