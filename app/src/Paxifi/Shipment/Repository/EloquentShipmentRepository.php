<?php namespace Paxifi\Shipment\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentShipmentRepository extends BaseModel {
    protected $table = 'shipments';

    protected $fillable = ['sticker_id', 'address', 'status'];

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
            'street' => $value['street'] ? : '',
            'city' => $value['city'] ? : '',
            'country' => $value['country'] ? : '',
            'postcode' => $value['postcode'] ? : '',
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