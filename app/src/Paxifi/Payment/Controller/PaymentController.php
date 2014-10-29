<?php namespace Paxifi\Payment\Controller;

use Paxifi\Payment\Exception\PaymentNotMatchException;
use Paxifi\Payment\Repository\PaymentRepository as Payment;
use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository as PaymentMethods;
use Paxifi\Payment\Repository\Validation\UpdatePaymentValidator;
use Paxifi\Payment\Transformer\PaymentTransformer;
use Paxifi\Store\Repository\Product\EloquentProductRepository;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;
use Paxifi\Payment\Repository\Factory\PaymentInvoiceFactory;

class PaymentController extends ApiController
{
    /**
     * Get specific payment information.
     *
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($payment) {
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

            $type = \Input::get('type', 'cash');

            /**
             * ! Todo:: Optimize the payment creation logic.
             *
             * Check if the order payment is exist;
             * If exist and has the same payment type, return the payment.
             * If don't have the same payment type, delete the old payment.
             * Create a new payment for this order.
             */
            if ($order->payment) {
                if ($order->payment->payment_method()->get()->first()->name == $type) {
                    return $this->setStatusCode(200)->respondWithItem($order->payment);
                } else {
                    $order->payment->delete();

                    // Fire delete sales event.
                    \Event::fire('paxifi.notifications.sales.delete', [$order->payment]);
                }
            }

            $newPayment = [
                'payment_method_id' => PaymentMethods::getMethodIdByName($type),
                'order_id' => $order->id,
                'details' => $this->translator->trans("payments.$type.create")
            ];

            if ($payment = Payment::create($newPayment)) {

                \DB::commit();

                return $this->setStatusCode(200)->respondWithItem($payment);
            }

            return $this->setStatusCode(500)->respondWithError('Payment create failed, please try it later.');

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        } catch (\Exception $e) {

            return $this->errorInternalError();

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

            if ($confirm == 1) {
                $products = $payment->order->products;

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

            return $this->setStatusCode(200)->respond([
                "success" => true
            ]);

        } catch (PaymentNotMatchException $e) {
            return $this->errorForbidden();
        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorInternalError();
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
    public function paypalPaymentConfirmation ($payment, $ipn)
    {
        try {
            $payment->status = 1;
            $payment->paypal_transaction_status = 1;
            $payment->paypal_transaction_id = $ipn['txn_id'];
            $payment->ipn = $ipn;

            $payment->save();

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
    }

    /**
     * Build invoice event
     */
    public function buildInvoice($payment) {
        $invoiceFactory = new PaymentInvoiceFactory($payment->order, $this->getInvoiceContentTranslation());

        $invoiceFactory->build();

        // Config email options
        $emailOptions = array(
            'template' => 'invoice.email',
            'context' => $this->translator->trans('email.invoice'),
            'to' => $payment->order->buyer_email,
            'data' => $invoiceFactory->getInvoiceData(),
            'attach' => $invoiceFactory->getPdfFilePath(),
            'as' => 'invoice_' . $payment->id . '.pdf',
            'mime' => 'application/pdf'
        );

        // Fire email invoice pdf event.
        \Event::fire('paxifi.email', array($emailOptions));
    }


    /**
     * @internal param \Paxifi\Order\Repository\EloquentOrderRepository $order
     *
     * @return array
     */
    public function getInvoiceContentTranslation()
    {
        return $this->translator->trans('pdf.content');
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
}