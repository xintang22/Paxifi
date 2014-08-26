<?php namespace Paxifi\Subscription\Transformer;

use League\Fractal\TransformerAbstract;

class SubscriptionTransformer extends TransformerAbstract
{
    public function transform($subscription)
    {
        return [
            'id' => $subscription->id,
            'driver_id' => $subscription->driver_id,
            'trial_start' => $subscription->trial_start,
            'trial_end' => $subscription->trial_end,
            'start' => $subscription->start,
            'canceled_at' => $subscription->canceled_at,
            'ended_at' => $subscription->ended_at,
            'current_period_start' => $subscription->current_period_start,
            'current_period_end' => $subscription->current_period_end,
            'cancel_at_period_end' => $subscription->cancel_at_period_end,
            'status' => $subscription->status,

        ];
    }

    public function transformSubscriptionPlan($id)
    {

    }
} 