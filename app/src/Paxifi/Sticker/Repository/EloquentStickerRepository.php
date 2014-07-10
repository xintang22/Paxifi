<?php namespace Paxifi\Sticker\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentStickerRepository extends BaseModel implements StickerRepositoryInterface
{
    /**
     * @var string
     */
    protected $table = 'stickers';

    /**
     * @var array
     */
    protected $fillable = ['driver_id', 'image', 'pdf', 'image_path', 'pdf_path'];

    /**
     * Relationship
     *
     * One sticker belongs to one driver.
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipments()
    {
        return $this->hasMany('Paxifi\Shipment\Repository\EloquentShipmentRepository', 'sticker_id', 'id');
    }
}