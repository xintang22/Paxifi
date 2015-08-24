<?php namespace Paxifi\Feedback\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentFeedbackRepository extends BaseModel
{
    /**
     * Table
     *
     * @var string
     */
    protected $table = "feedbacks";

    /**
     * Fillable
     *
     * @var array
     */
    protected $fillable = ['driver_id', 'payment_id', 'buyer_email', 'feedback', 'comment'];

    /**
     * Feedback - Driver many to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id', 'id');
    }

    /**
     * Feedback - Payment one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo('Paxifi\Payment\Repository\EloquentPaymentRepository', 'payment_id');
    }
} 