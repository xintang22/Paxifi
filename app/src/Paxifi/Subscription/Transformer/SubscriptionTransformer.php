<?php namespace Paxifi\Subscription\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Subscription\Repository\EloquentPlanRepository;
use Paxifi\Subscription\Repository\EloquentSubscriptionRepository;

class SubscriptionTransformer extends TransformerAbstract
{
    public function transform(EloquentSubscriptionRepository $subscription)
    {
        return [
            'id' => $subscription->id,
            'plan' => $this->transformSubscriptionPlan($subscription->plan_id),
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
            'created_at' => $subscription->created_at,
        ];
    }

    public function transformSubscriptionPlan($id)
    {
        return EloquentPlanRepository::find($id);
    }
} 