<?php namespace Paxifi\Subscription\Controller;

use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use Paxifi\Support\Controller\ApiController;

class SubscriptionController extends ApiController
{

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