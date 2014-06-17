<?php namespace Paxifi\Subscription\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentPlanRepository extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "subscription_plans";

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany('Paxifi\Subscription\Repository\EloquentSubscriptionRepository', 'plan_id', 'id');
    }
}