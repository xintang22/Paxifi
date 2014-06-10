<?php namespace Paxifi\Order\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentOrderRepository extends BaseModel implements OrderRepositoryInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('total_items', 'total_costs', 'total_sales', 'commission', 'profit', 'buyer_email', 'feedback', 'comment', 'status');

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = array(
        'total_items' => 'required',
        'total_costs' => 'required',
        'total_sales' => 'required',
        'buyer_email' => 'email',
    );

    /**
     * Products belong to this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany('Paxifi\Store\Repository\Product\EloquentProductRepository', 'order_items', 'order_id', 'product_id')
            ->withPivot(array('quantity'))->withTimestamps();
    }

    /**
     * Setup event bindings.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new OrderRepositoryObserver(\App::make('Paxifi\Support\Commission\CalculatorInterface')));
    }

    public function getTotalCosts()
    {
        return $this->total_costs;
    }

    public function getTotalSales()
    {
        return $this->total_sales;
    }
}