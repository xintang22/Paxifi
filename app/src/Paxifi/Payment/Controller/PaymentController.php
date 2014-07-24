<?php namespace Paxifi\Payment\Controller;

use Paxifi\Payment\Repository\PaymentRepository as Payment;
use Paxifi\Payment\Repository\EloquentPaymentMethodsRepository as PaymentMethods;
use Paxifi\Payment\Transformer\PaymentTransformer;
use Paxifi\Support\Controller\ApiController;

class PaymentController extends ApiController
{

    /**
     * Cash Payment.
     *
     * @param $order
     *
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cash($order, $status = 0)
    {
        try {
            \DB::beginTransaction();

            $new_payment = [
                'payment_method_id' => 1,
                'order_id' => $order->id,
                'status' => $status,
                'details' => "Some details"
            ];

            if ($payment = Payment::create($new_payment)) {
                \DB::commit();

                return $this->setStatusCode(200)->respond([
                    'success' => true,
                    'message' => 'Payment created successfully.',
                    'payment_id' => $payment->id
                ]);
            }

            return $this->setStatusCode(200)->respond([]);
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
    public function updatePayment($payment)
    {

        try {
            \DB::beginTransaction();

            $driver = $this->getAuthenticatedDriver();

            $inputs = \Input::only('status');

            $payment->status = $inputs['status'];

            $payment->save();

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
}