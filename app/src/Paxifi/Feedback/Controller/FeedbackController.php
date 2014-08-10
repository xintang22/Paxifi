<?php namespace Paxifi\Feedback\Controller;

use Paxifi\Feedback\Repository\EloquentFeedbackRepository as Feedback;
use Paxifi\Feedback\Transformer\FeedbackTransformer;
use Paxifi\Payment\Repository\Validation\CreatePaymentFeedbackValidator;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class FeedbackController extends ApiController {

    /**
     * Update feedback after the passenger paid the order by cash.
     *
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedback($payment)
    {
        try {
            \DB::beginTransaction();

            $inputs = \Input::all();
            $inputs['payment_id'] = $payment->id;
            $inputs['driver_id'] = \Input::get('driver_id');

            with(new CreatePaymentFeedbackValidator())->validate($inputs);

            $feedback = Feedback::create($inputs);

            if (\Input::has('feedback'))
                \Event::fire('paxifi.drivers.rating', [$feedback]);

            \DB::commit();

            return $this->setStatusCode(200)->respond([
                "success" => true,
                "message" => "Feedback has been sent."
            ]);

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
    }

    /**
     * Get all driver's commments
     *
     * @param $driver
     *
     * @return string
     */
    public function comments($driver) {
        try {

            $comments = $driver->comments()->toArray();

            return $this->setStatusCode(200)->respond(
                [
                    'success' => true,
                    "comments" => $comments
                ]
            );

            die;
        } catch (\Exception $e) {
            return $this->setStatusCode(500)->respondWithError($e->getMessage());
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new FeedbackTransformer();
    }
}