<?php namespace Paxifi\Notification\Repository;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Paxifi\Support\Repository\BaseModel;

class EloquentNotificationRepository extends BaseModel {

    use SoftDeletingTrait;

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string
     */
    protected $table = 'notifications';

    /**
     * @var array
     */
    protected $fillable = ['driver_id', 'sales', 'ranking', 'stock_reminder', 'emails', 'billing'];

    /**
     * @var array
     */
    protected $rules = [];


    /**
     * Driver-Notifications relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id');
    }

    /**
     * @param $payment_id
     *
     * @return mixed
     */
    public function findByPaymentId($payment_id)
    {
        return $this->where('sales', '=', $payment_id)->get()->toArray();
    }
}