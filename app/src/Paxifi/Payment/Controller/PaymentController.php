<?php namespace Paxifi\Payment\Controller;

use Paxifi\Payment\Repository\PaymentRepository as Payment;
use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository as PaymentMethods;
use Paxifi\Payment\Repository\Validation\CreatePaymentValidator;
use Paxifi\Payment\Transformer\PaymentTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;
use Paxifi\Payment\Repository\Factory\PaymentInvoiceFactory;

class PaymentController extends ApiController
{

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

            $type = \Input::get('type');

            $new_payment = [
                'payment_method_id' => PaymentMethods::getMethodIdByName($type),
                'order_id' => $order->id,
                'details' => "A new cash payment"
            ];

            with(new CreatePaymentValidator())->validate($new_payment);

            if ($payment = Payment::create($new_payment)) {

                \DB::commit();

                return $this->setStatusCode(200)->respond([
                    'success' => true,
                    'message' => 'Payment created successfully.',
                    'payment_id' => $payment->id
                ]);
            }
        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
    }


    public function cancelCash($driver = null)
    {

    }

    /**
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay($payment)
    {

        try {
            \DB::beginTransaction();

            $driver = $this->getAuthenticatedDriver();

            dd($payment);

            $inputs = \Input::only('status');

            $payment->status = $inputs['status'];

            $payment->save();

            \DB::commit();

            return $this->setStatusCode(200)->respond([
                "success" => true,
                "message" => "Payment success."
            ]);

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
    public function email($payment)
    {
        try {
            \DB::beginTransaction();

            $buyer_email = \Input::get('buyer_email');

            if (empty($buyer_email))

                return;

            if ($payment->status) {

                $payment->order->setBuyerEmail($buyer_email)
                    ->save();

                $invoiceFactory = new PaymentInvoiceFactory($payment->order, $this->getInvoiceContentTranslation());

                $invoiceFactory->build();

                // Config email options
                $emailOptions = array(
                    'template' => 'invoice.email',
                    'context' => $this->translator->trans('email.invoice'),
                    'to' => $buyer_email,
                    'data' => $invoiceFactory->getInvoiceData(),
                    'attach' => $invoiceFactory->getPdfFilePath(),
                    'as' => 'invoice_' . $payment->id . '.pdf',
                    'mime' => 'application/pdf'
                );

                // Fire email invoice pdf event.
                \Event::fire('paxifi.email', array($emailOptions));

                \DB::commit();

                return $this->setStatusCode(200)->respond(
                    [
                        "success" => true,
                    ]
                );
            }

            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.invoice.invoice_not_available', ['order_id' => $order->id]));

        } catch (\Exception $e) {
            return $this->errorWrongArgs($e->getMessage());
        }
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