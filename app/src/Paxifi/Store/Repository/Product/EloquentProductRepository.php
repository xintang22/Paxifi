<?php namespace Paxifi\Store\Repository\Product;

use Paxifi\Support\Repository\BaseModel;
use Paxifi\Tax\Repository\TaxRate;
use Paxifi\Tax\TaxableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class EloquentProductRepository extends BaseModel implements ProductRepositoryInterface, TaxableInterface
{
    use SoftDeletingTrait;

    /**
     * Paginate default per page.
     *
     * @var int
     */
    protected $perPage = 6;

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name', 'photos', 'description', 'unit_price', 'average_cost', 'inventory', 'tax_amount', 'driver_id', 'category_id', 'weight');

    /**
     * The data validation rules
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Serialize the photos.
     *
     * @param $value
     */
    public function setPhotosAttribute($value)
    {
        $this->attributes['photos'] = serialize($value);
    }

    /**
     * Returns un-serialized photos.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getPhotosAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Define a one-to-many relationship with Costs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costs()
    {
        return $this->hasMany('Paxifi\Store\Repository\Product\Cost\EloquentCostRepository', 'product_id', 'id');
    }

    /**
     * Define an inverse relationship with Driver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id');
    }

    /**
     * Orders the product belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany('Paxifi\Order\Repository\EloquentOrderRepository', 'order_items', 'product_id', 'order_id')
            ->withPivot(array('quantity'))->withTimestamps();
    }

    /**
     * @{@inheritdoc }
     */
    public function updateInventory($amount = 1)
    {
        if ($this->inventory == 0) return $this;

        for ($i = 0; $i < $amount; $i++) {
            $this->updateSingleCostInventory();
        }

        return $this;
    }

    /**
     * Decrease the inventory for a single product cost.
     */
    private function updateSingleCostInventory()
    {
        $cost = self::find($this->id)->costs->filter(function ($cost) {
            return $cost->inventory > 0;
        })->first();

        $cost->inventory--;

        $cost->save();
    }

    /**
     * Get the product's tax rate.
     *
     * @return \Paxifi\Tax\Repository\TaxRateInterface
     */
    public function getTaxRate()
    {
        return new TaxRate($this->tax_amount, $this->driver->tax_included_in_price);
    }
}