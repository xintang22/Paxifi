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
    protected $fillable = ['driver_id', 'type_id', 'value', 'type'];

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * The type which is the name inside notification type table.
     *
     * @var
     */
    protected $type;

    /**
     * Set type value.
     */
    public function setTypeAttribute()
    {
        $this->attributes['type_id'] = EloquentNotificationTypeRepository::findByType($this->type)->id;
    }

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
     * One to one relationship for one notification belongs to one notification type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->belongsTo('Paxifi\Notification\Repository\EloquentNotificationTypeRepository', 'type_id');
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