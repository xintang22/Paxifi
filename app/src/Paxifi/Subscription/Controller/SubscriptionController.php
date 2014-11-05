<?php namespace Paxifi\Subscription\Controller;

use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use Paxifi\Support\Controller\ApiController;

class SubscriptionController extends ApiController
{
    /**
     * Get subscription.
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        if ($subscription = $driver->subscription) {
            return $this->setStatusCode(200)->respondWithItem($subscription);
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new SubscriptionTransformer();
    }
}