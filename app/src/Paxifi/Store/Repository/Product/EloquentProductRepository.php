<?php namespace Paxifi\Store\Repository\Product;

use Paxifi\Support\Repository\BaseModel;

class EloquentProductRepository extends BaseModel implements ProductRepositoryInterface
{
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
    protected $fillable = array('name', 'photos', 'description', 'price', 'average_cost', 'quantity', 'tax', 'driver_id', 'category_id');

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
}