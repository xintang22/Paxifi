<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Support\Controller\BaseApiController;

class RatingController extends BaseApiController
{
    /**
     * Rate the store/driver.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rating($driver)
    {
        switch (\Input::get('type')) {
            case 'down':
                $driver->thumbsDown();

                return $this->respond(array('success' => true));

            case 'up':
            default:
                $driver->thumbsUp();

                return $this->respond(array('success' => true));

        }
    }
}