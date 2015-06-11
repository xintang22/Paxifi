<?php

namespace Paxifi\Stripe\Controller;

// Exceptions
use Illuminate\Support\Facades\DB;
use Paxifi\OnlinePayment\Controller\OnlinePaymentController;
use Paxifi\Payment\Exception\PaymentNotFoundException;
use Paxifi\Payment\Exception\PaymentNotSuccessException;
use Paxifi\Payment\Exception\PaymentNotValidException;
use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository;
use Paxifi\Payment\Transformer\PaymentTransformer;
use Paxifi\Store\Exception\StoreNotFoundException;

use Paxifi\Payment\Repository\EloquentPaymentRepository;
use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Stripe\Exception\RefundException;
use Paxifi\Stripe\Exception\RefundNotValidException;
use Paxifi\Stripe\Repository\EloquentStripeRepository;
use Stripe\Charge;
use Stripe\HttpClient\CurlClient;
use Stripe\Stripe;
use StripeTransformer;
use Input, Config, View;

class StripeController extends OnlinePaymentController
{
    private $stripeClient;

    private $stripeSecretKey;

    private $stripeConnectApi;

    private $stripeLiveMode;

    private $stripeRedirectUrl;

    protected $applicationFeeRate;

    function __construct()
    {
        parent::__construct();

        $this->stripeClient = CurlClient::instance();

        $this->applicationFeeRate = !!Config::get('stripe.application.fee.rate') ? (int)Config::get('stripe.application.fee.rate') : 0;

        $this->stripeSecretKey = Config::get('stripe.secret.key');

        $this->stripeConnectApi = Config::get('stripe.connect.api');

        $this->stripeLiveMode = Config::get('stripe.live.mode');

        $this->stripeClientId = Config::get('stripe.client.id');

        $this->stripeRedirectUrl = Config::get('stripe.redirect.url');

        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Authorize the driver stripe account to connect to Paxifi Platform.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function authorize()
    {
        try {
            \DB::beginTransaction();

            $authUrl = $this->stripeConnectApi . 'oauth/token';

            $params = [
            'client_secret' => $this->stripeSecretKey,
            'code' => Input::get('code'),
            'grant_type' => 'authorization_code'
            ];

            $driver_id = Input::get('state');

            if ($driver = DriverRepository::findOrFail($driver_id)) {

                if ($driver->hasConnectStripe()) {
                    return View::make('appRedirect');
                }

                $response = $this->stripeClient->request('POST', $authUrl, [], $params, false);

                if ($response[1] == '200') {

                    $data = json_decode($response[0], true);

                    $data['driver_id'] = $driver->id;

                    if ($stripe = EloquentStripeRepository::create($data)) {

                        $driver->available_payment_methods()->attach(EloquentPaymentMethodsRepository::getMethodIdByName('stripe'));

                        \DB::commit();

                        return View::make('appRedirect');
                    }
                } else {
                    return $this->setStatusCode($response[1])->respondWithError(json_decode($response[0])->error);
                }
            } else {
                throw new StoreNotFoundException();
            }
        } catch (StoreNotFoundException $e) {
            return $this->setStatusCode(404)->respondWithError($this->translator->trans('responses.driver.not_found'));
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }

    }

    /**
     * Charge stripe.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function charge()
    {
        try {
            \DB::beginTransaction();

            $driver_id = Input::get('driver_id');

            if ($driver_id && $driver = EloquentDriverRepository::find($driver_id)) {

                $stripeCharge = [
                'amount' => Input::get('amount'),
                'currency' => Input::get('currency'),
                'source' => Input::get('id'),
                'destination' => $driver->stripe->stripe_user_id
                ];

                if (!$payment = EloquentPaymentRepository::find(Input::get('payment_id'))) {
                    throw new PaymentNotFoundException();
                } else {
                    if (isset($this->applicationFeeRate) && (int)$this->applicationFeeRate > 0) {
                        array_push($stripeCharge, ['application_fee' => round(Input::get('amount') * $this->applicationFeeRate / 100)]);
                    }

                    if ($charge = Charge::create($stripeCharge)->__toArray()) {

                        // Validate Charge
                        if ($this->validateCharge($charge, $payment, $driver)) {
                            $payment->transaction_details = $charge;
                            // Update payment status.
                            $payment->success();

                            \Event::fire('paxifi.payment.confirmed', [$payment]);

                            $payment->type = "sales";
                            \Event::fire('paxifi.notifications.sales', [$payment]);

                            \DB::commit();

                            return $this->setStatusCode(200)->respond(["success" => true]);
                        } else {
                            throw new PaymentNotValidException();
                        }
                    } else {
                        throw new PaymentNotSuccessException();
                    }
                }
            } else {
                throw new StoreNotFoundException();
            }
        } catch (StoreNotFoundException $e) {
            return $this->setStatusCode(404)->respondWithError($this->translator->trans('responses.store.not_found'));
        } catch (PaymentNotFoundException $e) {
            return $this->setStatusCode(404)->respondWithError($this->translator->trans('responses.payment.not_found'));
        } catch (PaymentNotValidException $e) {
            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.payment.not_valid'));
        } catch (PaymentNotSuccessException $e) {
            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.payment.not_success'));
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Disconnect driver
     *
     * @param null $driver
     * @return \Illuminate\Http\JsonResponse
     */
    public function deauthorize($driver = null)
    {

        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $deauthUrl = $this->stripeConnectApi . 'oauth/deauthorize';

            $params = [
            'client_secret' => $this->stripeSecretKey,
            'client_id' => $this->stripeClientId,
            'stripe_user_id' => $driver->stripe->stripe_user_id
            ];

            $response = $this->stripeClient->request('POST', $deauthUrl, [], $params, false);

            if ($response[1] == '200') {

                $driver->stripe->delete();

                $driver->available_payment_methods()->detach(EloquentPaymentMethodsRepository::getMethodIdByName('stripe'));

                \DB::commit();

                return $this->setStatusCode(204)->respond($this->translator->trans('responses.stripe.disconnect_success'));
            } else {
                return $this->setStatusCode($response[1])->respond($this->translator->trans('responses.stripe.disconnect_failed'));
            }
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
        return new StripeTransformer();
    }

    /**
     * Validate stripe charge.
     *
     * @param $charge
     * @param $payment
     * @param $driver
     * @return bool
     */
    private function validateCharge($charge, $payment, $driver)
    {

        return ($charge['status'] == 'succeeded') &&
        ($charge['livemode'] == $this->stripeLiveMode) &&
        ($charge['paid'] == true) &&
        (strtolower($charge['currency']) == strtolower($driver->currency)) &&
        (round($charge['amount']) == (round($payment->order->total_sales * 100))) &&
        ($charge['destination'] == $driver->stripe->stripe_user_id);
    }

    public function refund($driver = null)
    {
        try {
            DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $paymentId = Input::get('payment_id');

            if ($payment = EloquentPaymentRepository::find($paymentId)) {

                if ($payment->refunded) {
                    return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.stripe.refund_not_available'));
                }

                if ($payment->status == 1 && $payment->order->OrderDriver()->id == $driver->id) {
                    $details = $payment->transaction_details;

                    $ch = Charge::retrieve($details['id']);

                    if ($refund = $ch->refunds->create(array('amount' => round($payment->order->total_sales * 100), 'reverse_transfer' => true))->__toArray()) {

                        if ($this->validateRefund($refund, $payment, $driver)) {

                            $payment->refunded();

                            DB::commit();

                            return $this->setStatusCode(200)->respond(with(new PaymentTransformer())->transform($payment));
                        } else {
                            throw new RefundNotValidException();
                        }
                    }
                } else {
                    throw new RefundException();
                }
            } else {
                throw new PaymentNotFoundException();
            }
        } catch (RefundException $e) {
            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.stripe.refund_failed'));
        } catch (RefundNotValidException $e) {
            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.stripe.refund_failed'));
        } catch (\Exception $e) {
            return $this->setStatusCode(500)->respondWithError($e->getMessage());
        }

    }

    /**
     * Validate refund.
     *
     * @param $refund
     * @param $payment
     * @param $driver
     * @return bool
     */
    public function validateRefund($refund, $payment, $driver)
    {
        return ($refund['object'] == 'refund') &&
        (strtolower($refund['currency']) == strtolower($driver->currency)) &&
        (round($refund['amount']) == (round($payment->order->total_sales * 100)));
    }
}