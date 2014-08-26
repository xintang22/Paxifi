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
     *
     * @var array
     */
    protected $fillable = [ "plan_id", "driver_id", "trial_start", "start", "canceled_at", "ended_at", "current_period_start", "current_period_end", "txn_type", "payer_id", "ipn_track_id"];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Subscription - Subscription plans one to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo('Paxifi\Subscription\Repository\EloquentPlanRepository', 'plan_id', 'id');
    }

    /**
     * Subscription - Driver one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id', 'id');
    }

    public static function findSubscriptionByIpnTrackId($ipnTrackId)
    {
        return self::where('ipn_track_id', '=', $ipnTrackId)->get();
    }

} 