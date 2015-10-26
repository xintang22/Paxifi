<?php namespace Paxifi\Feedback\Controller;

use Paxifi\Feedback\Repository\FeedbackRepository as Feedback;
use Paxifi\Feedback\Transformer\FeedbackTransformer;
use Paxifi\Payment\Repository\Validation\CreatePaymentFeedbackValidator;
use Paxifi\Store\Repository\Driver\DriverRepository;
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
            if($payment->feedback) {
                return $this->setStatusCode(200)->respond([
                    "success" => true,
                    "isNew" => false
                ]);
            }

            \DB::beginTransaction();

            $inputs = \Input::except('buyer_email');
            $inputs['payment_id'] = $payment->id;
            $inputs['driver_id'] = \Input::get('driver_id');

            with(new CreatePaymentFeedbackValidator())->validate($inputs);

            $feedback = Feedback::create($inputs);

            if (\Input::has('feedback'))
                \Event::fire('paxifi.drivers.rating', [$feedback]);

            \DB::commit();

            return $this->setStatusCode(200)->respond([
                "success" => true,
                "isNew" => true,
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
            $cacheKey = [DriverRepository::getTable(), Feedback::getTable()];

            $count = 4;

            $page = \Input::get('page');

            if (\Input::has('page') && $page >= 1) {
                $comments = $driver->comments()->cacheTags($cacheKey)->remember(10)->skip(($page - 1) * $count)->take($count)->get()->toArray();
            } else {
                $comments = $driver->comments()->cacheTags($cacheKey)->remember(10)->get()->toArray();
            }

            return $this->setStatusCode(200)->respond(
                [
                    "success" => true,
                    "comments" => $comments,
                    "count" => $driver->comments()->get()->count()
                ]
            );

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