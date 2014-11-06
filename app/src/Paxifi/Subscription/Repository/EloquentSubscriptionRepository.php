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
    protected $fillable = ["plan_id", "driver_id", "trial_start", "trial_end", "start", "canceled_at", "ended_at", "current_period_start", "current_period_end", "ipn", "subscr_id", "status", "commission_start", "commission_end"];

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
        $this->status = $this->inTrial() ? 'trialing' : "active";
        $this->cancel_at_period_end = false;
        $this->save();
    }

    /**
     * Check whether account is in trialing status.
     *
     * @return bool
     */
    public function inTrial() {
        return !!(Carbon::now() <= $this->trial_end && Carbon::now() >= $this->trial_start );
    }

    /**
     * Expire subscription.
     */
    public function expired()
    {
        $this->status = "past_due";
        $this->cancel_at_period_end = true;
        $this->ended_at = Carbon::now();
        $this->save();
    }

    /**
     * Cancel subscription.
     */
    public function canceled()
    {
        $this->canceled_at = Carbon::now();
        $this->cancel_at_period_end = true;
        $this->status = "canceled";
        $this->save();
    }

    /**
     * @param EloquentPlanRepository $plan
     * @param EloquentDriverRepository $driver
     */
    public static function initiateTrial(EloquentPlanRepository $plan, EloquentDriverRepository $driver)
    {
        $now = Carbon::now();

        $subscription = new static();
        $subscription->driver_id = $driver->id;
        $subscription->plan_id = $plan->id;
        $subscription->trial_start = $now;
        $subscription->trial_end = $now->addDays($plan->trial_period_days);
        $subscription->start = $now;
        $subscription->current_period_start = $now->addDays($plan->trial_period_days);

        $subscription->save();
    }

    /**
     * Renew the subscription.
     *
     * @param EloquentPlanRepository   $plan
     * @param EloquentDriverRepository $driver
     */
    public function renewSubscription(EloquentPlanRepository $plan, EloquentDriverRepository $driver)
    {
        $now = Carbon::now();

        $this->current_period_start = $now;
        $this->cancel_at_period_end = false;

        switch($plan->interval) {
            case 'year':
                $this->current_period_end = $now->addYears($plan->interval_count);
                break;
            case 'month':
                $this->current_period_end = $now->addMonths($plan->interval_count);
                break;
            case 'day':
                $this->current_period_end = $now->addDays($plan->interval_count);
                break;
            default:
                ;
        }

        $this->active();
    }

    /**
     * Check whether the subscription need auto subscribe.
     *
     * @return bool
     */
    public function needChargeSubscription() {
        if (in_array($this->status, ['canceled', 'past_due'])) {
            return false;
        } else {
            if ($this->status == 'active') {

                return !!((Carbon::now() >= $this->current_period_end) && (Carbon::now()->subDay() < $this->current_period_end));

            } else {

                return !!((Carbon::now() >= $this->trial_end) && (Carbon::now()->subDay() < $this->trial_end));

            }
        }
    }

    /**
     * Check whether the need pay paxifi commission.
     *
     * @return bool
     */
    public function needChargeCommission() {
        if ($this->status == 'trialing') {
            return !!((Carbon::now() >= $this->trial_end) && (Carbon::now()->subDay() < $this->trial_end));
        } else {
            return !!((Carbon::now() >= $this->current_period_end) && (Carbon::now()->subDay() < $this->current_period_end));
        }
    }

    /**
     * @param EloquentPlanRepository $plan
     */
    public function subscribe(EloquentPlanRepository $plan) {
        if ($this->status == 'active') {
            $this->current_period_start = $this->current_period_end;
            $this->current_period_end = $this->current_period_end->addMonths($plan->interval_count);
        }

        if ($this->status == 'trialing') {
            $this->current_period_start = $this->trial_end;
            $this->current_period_end = $this->trial_end->addMonths($plan->interval_count);
        }

        $this->status = 'active';
        $this->canceled_at = null;
        $this->cancel_at_period_end = 0;

        $this->save();
    }

    public function getDates()
    {
        return array('created_at', 'updated_at', 'trial_start', 'trial_end', 'start', 'canceled_at', 'ended_at', 'current_period_start', 'current_period_end', 'current_period_start');
    }
}