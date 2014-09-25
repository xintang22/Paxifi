<?php
namespace Paxifi\Commission\Repository;


use Paxifi\Support\Repository\BaseModel;

class EloquentCommissionRepository extends BaseModel
{
    /**
     * Commission table.
     *
     * @var string
     */
    protected $table = 'commissions';

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = ['driver_id', 'total_commission', 'status', 'commission_ipn', 'commission_payment_id'];

    /**
     * Driver - Commission one to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id');
    }

    /**
     * Attributes
     *
     * Serialize the ipn
     *
     * @param $value
     */
    public function setCommissionIpnAttribute($value)
    {
        $this->attributes['commission_ipn'] = serialize($value);
    }

    /**
     * Returns un-serialized ipn.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getCommissionIpnAttribute($value)
    {
        return unserialize($value);
    }
} 