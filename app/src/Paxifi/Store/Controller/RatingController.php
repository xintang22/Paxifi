<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Support\Controller\BaseApiController;

class RatingController extends BaseApiController
{
    /**
     * Rate the store/driver.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rating($id)
    {

        if ($driver = DriverRepository::find($id)) {

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

        return $this->errorWrongArgs('Store does not exist.');
    }
}