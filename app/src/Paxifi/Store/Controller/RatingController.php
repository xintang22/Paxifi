<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Support\Controller\BaseApiController;

class RatingController extends BaseApiController
{
    /**
     * Rate the store/driver.
     *
     * @param $feedback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rating($feedback)
    {
        try {
            $driver = $feedback->driver;

            switch ($feedback->feedback) {
                case -1:
                    $driver->thumbsDown();

                    break;

                case 1:
                default:
                    $driver->thumbsUp();

            }

            $feedback->type = "thumbs";

            \Event::fire('paxifi.notifications.ranking', [$feedback]);

            return $this->respond(array('success' => true));
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }
}