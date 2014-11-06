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
    protected $fillable = ['driver_id', 'commissions', 'currency', 'status', 'commission_ipn', 'commission_payment_id', 'commission_start', 'commission_end'];

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

    /**
     * Attributes
     *
     * Serialize the ipn
     *
     * @param $value
     */
    public function setCaptureIpnAttribute($value)
    {
        $this->attributes['capture_ipn'] = serialize($value);
    }

    /**
     * Returns un-serialized ipn.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getCaptureIpnAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * @return array
     */
    public function getDates()
    {
        return array('commission_start', 'commission_end');
    }
} 