<?php

namespace Paxifi\Stripe\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentStripeRepository extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'stripe';

    /**
     * @var array
     */
    protected $fillable = ['driver_id', 'refresh_token', 'token_type', 'stripe_publishable_key', 'stripe_user_id', 'scope'];

    /**
     * @var array
     */
    protected $hidden = ['refresh_token'];

    /**
     * Has one - one relationship with driver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Driver\Repository\EloquentDriverRepository', 'driver_id');
    }
}