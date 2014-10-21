<?php namespace Paxifi\Subscription\Repository;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Repository\BaseModel;

class EloquentSubscriptionRepository extends BaseModel implements SubscriptionRepositoryInterface
{

    use SoftDeletingTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * @var array
     */
    protected $hidden = ['ipn'];

    /**
     *
     * @var array
     */
    protected $fillable = ["plan_id", "driver_id", "trial_start", "trial_end", "start", "canceled_at", "ended_at", "current_period_start", "current_period_end", "ipn", "subscr_id", "status"];

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

    /**
     * Find the subscriptions by tracking the subscr_id
     *
     * @param $subscrId
     *
     * @return mixed
     */
    public static function findSubscriptionBySubscrId($subscrId)
    {
        return self::where('subscr_id', '=', $subscrId)->get()->first();
    }

    /**
     * Attributes
     *
     * Serialize the ipn
     *
     * @param $value
     */
    public function setIpnAttribute($value)
    {
        $this->attributes['ipn'] = serialize($value);
    }

    /**
     * Returns un-serialized ipn.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getIpnAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Active subscription.
     */
    public function active()
    {
        $this->status = "active";
        $this->save();
    }

    /**
     * Expire subscription.
     */
    public function expired()
    {
        $this->status = "past_due";
        $this->save();
        $this->delete();
    }

    /**
     * Cancel subscription.
     */
    public function canceled()
    {
        $this->status = "canceled";
        $this->save();
    }

    /**
     * @param EloquentPlanRepository $plan
     * @param EloquentDriverRepository $driver
     */
    public static function initiateTrail(EloquentPlanRepository $plan, EloquentDriverRepository $driver)
    {
        $now = Carbon::now();

        $subsctiption = new static();
        $subsctiption->driver_id = $driver->id;
        $subsctiption->plan_id = $plan->id;
        $subsctiption->trial_start = $now;
        $subsctiption->trial_end = $now->addDays($plan->trial_period_days);
        $subsctiption->start = $now;

        $subsctiption->save();
    }
}