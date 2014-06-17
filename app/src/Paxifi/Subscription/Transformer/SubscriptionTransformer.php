<?php namespace Paxifi\Subscription\Transformer;

use League\Fractal\TransformerAbstract;

class SubscriptionTransformer extends TransformerAbstract
{
    public function transformer($subscription)
    {
        return [
            'id' => $subscription->id,
        ];
    }
} 