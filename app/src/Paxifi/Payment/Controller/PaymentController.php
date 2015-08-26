<?php namespace Paxifi\Payment\Controller;

use Paxifi\Payment\Exception\PaymentNotFoundException;
use Paxifi\Payment\Exception\PaymentNotMatchException;
use Paxifi\Payment\Repository\PaymentRepository as Payment;
use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository as PaymentMethods;
use Paxifi\Payment\Repository\Validation\UpdatePaymentValidator;
use Paxifi\Payment\Transformer\PaymentTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class PaymentController extends ApiController
{
    protected $queue;

    /**
     * Get specific payment information.
     *
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($payment)
    {
        return $this->respondWithItem($payment);
    }

    /**
     * @param $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function received($payment)
    {
        try {
            \DB::beginTransaction();

            if ($payment) {
                $payment->received();

                return $this->respondWithItem($payment);
            } else {
                throw new PaymentNotFoundException();
            }
        } catch(PaymentNotFoundException $e) {
            return $this->setStatusCode(404)->respondWithError($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * Get driver payment with driver_id and payment_id
     *
     * @param null $driver
     * @param $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function meShow($driver = null, $payment)
    {

        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        return $this->respondWithItem($payment);
    }

    /**
     * Cash Payment.
     *
     * @param $order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment($order)
    {

        try {
            \DB::beginTransaction();

            $data = \Input::get('data');


            // Check if payment method is available by the driver.
            if (!$order->OrderDriver()->paymentMethodAvailable($data['type'])) {
                return $this->setStatusCode(406)->respondWithError(trans('responses.' . $data['type'] .'.not_available'));
            }

            if ($order->payment) {
                if ($order->payment->status) {
                    return $this->setStatusCode(200)->respondWithItem($order->payment);
                } else {
                    if ($order->payment->payment_method()->get()->first()->name == $data['type']) {
                        return $this->setStatusCode(200)->respondWithItem($order->payment);
                    } else {
                        $order->payment->delete();

                        // Fire delete sales event.
                        \Event::fire('paxifi.notifications.sales.delete', [$order->payment]);
                    }
                }
            }

            $newPayment = $this->getPaymentData($data);

            if ($payment = Payment::create($newPayment)) {
                \DB::commit();

                return $this->setStatusCode(200)->respondWithItem($payment);
            }

            return $this->setStatusCode(500)->respondWithError('Payment create failed, please try it later.');

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        } catch (\Exception $e) {

            return $this->errorInternalError($e->getMessage());

        }
    }

    /**
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($payment)
    {
        try {
            \DB::beginTransaction();

            if ($this->getAuthenticatedDriver()->email != $payment->order->OrderDriver()->email) {
                throw new PaymentNotMatchException('Payment owner not match');
            }

            if ($payment->status == 1) {
                return $this->errorWrongArgs('Payment has been completed already.');
            }

            with(new UpdatePaymentValidator())->validate(\Input::only('confirm'));

            $confirm = \Input::get('confirm', 1);
            $payment->status = $confirm;
            $payment->save();

            $order = $payment->order;
            $order->status = 1;
            $order->save();

            if ($confirm == 1) {
                \Event::fire('paxifi.payment.confirmed', [$payment]);

                // If need get invoice:
                if ($payment->invoice && !empty($payment->invoice_email)) {
                    \Event::fire('paxifi.build.invoice', [$payment]);
                }
            }

            \DB::commit();

            return $this->setStatusCode(200)->respond([
                "success" => true
            ]);

        } catch (PaymentNotMatchException $e) {
            return $this->errorForbidden();
        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Get order invoice email with a copy of invoice pdf file.
     *
     * @param $payment
     *
     * @internal param $id
     * @return mixed
     */
    public function invoice($payment)
    {
        try {
            \DB::beginTransaction();

            $buyer_email = \Input::get('buyer_email');

            if (empty($buyer_email))
                return;

            if ($payment->status) {
                $payment->order->setBuyerEmail($buyer_email)
                    ->save();

                \Event::fire('paxifi.build.invoice', $payment);

                \DB::commit();

                return $this->setStatusCode(200)->respond(
                    ["success" => true]
                );
            }

            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.invoice.invoice_not_available', ['payment_id' => $payment->id]));

        } catch (\Exception $e) {

            return $this->errorWrongArgs($e->getMessage());

        }
    }

    /**
     * Cancel the payment.
     *
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($payment)
    {
        try {
            \DB::beginTransaction();

            if ($payment->status != 1) {

                $paymentNotDeleted = $payment;

                if ($payment->delete()) {

                    // Fire delete sales event.
                    \Event::fire('paxifi.notifications.sales.delete', [$payment]);
                    \DB::commit();

                    return $this->setStatusCode(200)->respondWithItem($paymentNotDeleted);
                }

                return $this->setStatusCode(400)->respondWithError('Payment delete ');

            }

            return $this->errorWrongArgs('Payment has been completed. You cannot cancel it.');

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
    }


    /**
     * Verify paypal payment status completed.
     *
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify($payment)
    {
        try {
            if ($payment->status && $payment->paypal_transaction_status) {

                return $this->respondWithItem($payment);

            }

            return $this->setStatusCode(403)->respondWithError("Payment not success.");

        } catch (\Exception $e) {

            return $this->respondWithError($e->getMessage());

        }
    }

    /**
     * Confirm the paypal payment.
     *
     * @param $payment
     * @param $ipn
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paypalPaymentConfirmation($payment, $ipn)
    {
        try {
            \DB::beginTransaction();
            $payment->status = 1;
            $payment->paypal_transaction_status = 1;
            $payment->paypal_transaction_id = $ipn['txn_id'];
            $payment->ipn = $ipn;

            $payment->save();

            $order = $payment->order;
            $order->status = 1;
            $order->save();

            \Event::fire('paxifi.notifications.sales', [$payment]);

            \DB::commit();

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
        return new PaymentTransformer();
    }

    /**
     * Get payment object data.
     *
     * @param $data
     * @return array
     */
    private function getPaymentData($data)
    {

        $newPayment = [
            'payment_method_id' => PaymentMethods::getMethodIdByName($data['type']),
            'order_id' => $data['id'],
            'details' => $this->translator->trans("payments.{$data['type']}.create")
        ];

        if ($data['invoice'] && $data['invoice_email']) {
            $newPayment['invoice'] = $data['invoice'];
            $newPayment['invoice_email'] = $data['invoice_email'];
        }

        return $newPayment;
    }
}