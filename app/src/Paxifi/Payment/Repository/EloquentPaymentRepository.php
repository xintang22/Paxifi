<?php namespace Paxifi\Payment\Repository;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Paxifi\Support\Repository\BaseModel;

class EloquentPaymentRepository extends BaseModel {

    use SoftDeletingTrait;

    protected $table = 'payments';

    protected $fillable = ['order_id', 'payment_method_id', 'status', 'details', 'paypal_transaction_id', 'paypal_transaction_status', 'ipn'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Payment - Order one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('Paxifi\Order\Repository\EloquentOrderRepository', 'order_id');
    }

    /**
     * Payment - Payment Method one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment_method()
    {
        return $this->hasOne('Paxifi\Payment\Repository\EloquentPaymentMethodsRepository', 'id', 'payment_method_id');
    }

    /**
     * Payment - Feedback Method one to one relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function feedback()
    {
        return $this->belongsTo('Paxifi\Feedback\Repository\EloquentFeedbackRepository', 'id', 'payment_id');
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
     * @param $order
     *
     * @return mixed
     */
    public static function findPaymentOrderId($order)
    {
        return self::where('order_id', '=', $order->order_id)->get();
    }

    /**
     * Setup event bindings.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new PaymentRepositoryObserve());
    }

} 