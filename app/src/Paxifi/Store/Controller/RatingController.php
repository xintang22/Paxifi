<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Support\Controller\BaseApiController;

class RatingController extends BaseApiController
{
    /**
     * Rate the store/driver.
     *
     * @param $order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rating($order)
    {
        $driver = $order->OrderDriver();

        switch ($order->feedback) {
            case -1:
                $driver->thumbsDown();

                break;

            case 1:
            default:
                $driver->thumbsUp();

        }

        \Event::fire('paxifi.notifications.ranking', [$order]);

        return $this->respond(array('success' => true));
    }
}