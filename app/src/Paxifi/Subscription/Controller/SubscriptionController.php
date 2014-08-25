<?php namespace Paxifi\Subscription\Controller;

use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use Paxifi\Support\Controller\ApiController;

class SubscriptionController extends ApiController
{

    public function subscribe()
    {
        $subscribe = \Input::all();

        \Log::useFiles(storage_path().'/logs/sub-'.time().'.txt');

        \Log::info($subscribe);
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