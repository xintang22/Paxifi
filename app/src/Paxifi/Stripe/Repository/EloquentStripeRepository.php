<?php

namespace Paxifi\Stripe\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentStripeRepository extends BaseModel
{
    protected $table = 'stripe';

    protected $fillable = ['driver_id', 'refresh_token', 'token_type', 'stripe_publishable_key', 'stripe_user_id', 'scope'];

    protected $hidden = ['refresh_token'];

    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Driver\Repository\EloquentDriverRepository', 'driver_id');
    }
}