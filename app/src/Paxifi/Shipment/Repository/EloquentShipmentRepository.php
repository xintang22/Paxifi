<?php namespace Paxifi\Shipment\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentShipmentRepository extends BaseModel {
    protected $table = 'shipments';

    protected $fillable = ['sticker_id', 'address', 'status', 'paypal_payment_id', 'paypal_payment_status', 'paypal_payment_details'];

    protected $hidden = ['paypal_payment_details'];
    // Relationship
    public function sticker()
    {
        return $this->belongsto('Paxifi\Sticker\Repository\EloquentStickerRepository', 'sticker_id');
    }

    /**
     * Serialize the address.
     *
     * @param $value
     */
    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = serialize([
            'street' => isset($value['street']) ? $value['street'] : '',
            'city' => isset($value['city']) ? $value['city'] : '',
            'country' => isset($value['country']) ? $value['country'] : "US",
            'postcode' => isset($value['postcode']) ? $value['postcode'] : '',
        ]);
    }

    /**
     * Returns un-serialized address.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getAddressAttribute($value)
    {
        return unserialize($value);
    }
}