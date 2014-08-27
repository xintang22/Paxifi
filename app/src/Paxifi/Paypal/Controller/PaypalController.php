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

                        \Event::fire('paxifi.paypal.subscription.' . $ipn['txn_type'], [$driver, $ipn]);

                    }
                },
                function() use ($listener) {

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