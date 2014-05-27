<?php namespace Paxifi\Store\Repository\Product\Cost;

use Paxifi\Support\Repository\BaseModel;

class EloquentCostRepository extends BaseModel
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_costs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('cost', 'quantity', 'product_id');

    /**
     * Define an inverse relationship with Driver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function product()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Product\EloquentProductRepository', 'product_id');
    }

    /**
     * Setup event bindings.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new CostRepositoryObserver());
    }

}