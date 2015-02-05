<?php namespace Paxifi\Paypal\Controller;

use Illuminate\Support\Facades\Input;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Paypal\Exception\PaymentNotValidException;
use Paxifi\Paypal\Paypal;
use Paxifi\Shipment\Repository\EloquentShipmentRepository;
use Paxifi\Shipment\Repository\Validation\CreateShipmentValidator;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Store\Repository\Product\EloquentProductRepository;
use Paxifi\Support\Controller\ApiController;
use PayPal\Ipn\Listener;
use PayPal\Ipn\Message;
use PayPal\Ipn\Verifier\CurlVerifier;
use PayPal\Ipn\Verifier\SocketVerifier;

class PaypalController extends ApiController
{
    protected $paypal;

    function __construct(Paypal $paypal) {
        parent::__construct();
        $this->paypal = $paypal;
    }

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
            $verifier = new SocketVerifier;
            // $ipn = \Input::all();

            $ipnMessage = Message::createFromGlobals(); // uses php://input      

            $verifier->setIpnMessage($ipnMessage);
            $verifier->setEnvironment(\Config::get('app.paypal_mode'));

            $listener->setVerifier($verifier);

            $listener->onVerifiedIpn(function() use ($listener) {
                $ipn = [];

                $messages = $listener->getVerifier()->getIpnMessage();

                parse_str($messages, $ipn);

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

                        $products = $order->products;

                        $products->map(function ($product) {
                            // Fires an event to update the inventory.
                            \Event::fire('paxifi.product.ordered', array($product, $product['pivot']['quantity']));

                            // Fires an event to notification the driver that the product is in low inventory.
                            if (EloquentProductRepository::find($product->id)->inventory <= 5) {
                                \Event::fire('paxifi.notifications.stock', array($product));
                            }
                        });
                    }

                    \DB::commit();
                }
            });

            $listener->listen(function() use ($listener) {

                $resp = $listener->getVerifier()->getVerificationResponse();

            }, function() use($listener) {

                 // on invalid IPN (somethings not right!)
                 $report = $listener->getReport();
                 $resp = $listener->getVerifier()->getVerificationResponse();
                 \Log::useFiles(storage_path().'/logs/'. 'error-'. time(). '.txt');
                 \Log::info($report);

                 return $this->setStatusCode(400)->respondWithError('Payment failed.');
            });
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
     * @param $verification
     * @param $driver
     * @return bool
     */
    private function verifyStickerPayment($verification, $driver) {
        $result = ($verification['transactions'][0]['amount']['total'] == $driver->getStickerPrice() &&
                    $verification['transactions'][0]['amount']['currency'] == $driver->currency &&
                    $verification['transactions'][0]['related_resources'][0]['sale']['state'] == 'completed');
        return true;
        return !! $result;
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

            $new_shipment = [
                "sticker_id" => $driver->sticker->id,
                "address" => \Input::get('address', $driver->address),
                "status" => "waiting",
                "paypal_payment_status" => "pending"
            ];

            if (!$payment = Input::get('payment')) return $this->errorWrongArgs();

            with(new CreateShipmentValidator())->validate($new_shipment);

            if ($payment['state'] == 'approved' && $payment['intent'] == 'sale') {

                if (!$verification = $this->paypal->getStickerPaymentVerification($payment['id'])) {
                    throw new PaymentNotValidException('PayPal Payment not valid.');
                }

                if($this->verifyStickerPayment($verification, $driver)) {

                    \Event::fire('paxifi.paypal.sticker.payment', [$new_shipment]);

                    \DB::commit();

                    return $this->setStatusCode(200)->respond(['success' => true]);
                }
            }

            throw new PaymentNotValidException('PayPal Payment not valid.');

        } catch (PaymentNotValidException $e) {
            return $this->setStatusCode(402)->respondWithError($e->getMessage());
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