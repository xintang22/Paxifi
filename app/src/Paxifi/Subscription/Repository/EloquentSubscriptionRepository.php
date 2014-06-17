<?php namespace Paxifi\Subscription\Repository;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Paxifi\Support\Repository\BaseModel;

class EloquentSubscriptionRepository extends BaseModel {

    use SoftDeletingTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Define belongsTo relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo('Paxifi\Subscription\Repository\EloquentPlanRepository', 'plan_id', 'id');
    }

} 