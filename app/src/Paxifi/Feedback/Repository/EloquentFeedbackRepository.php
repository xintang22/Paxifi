<?php namespace Paxifi\Feedback\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentFeedbackRepository extends BaseModel {
    protected $table = "feedbacks";

    protected $fillable = ['driver_id', 'payment_id', 'buyer_email', 'feedback', 'comment'];

    public function driver() {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id', 'id');
    }

    public function payment() {
        return $this->belongsTo('Paxifi\Payment\Repository\EloquentPaymentRepository', 'payment_id');
    }
} 