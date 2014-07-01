<?php namespace Paxifi\Order\Repository;

use Paxifi\Store\Repository\Product\EloquentProductRepository;
use Paxifi\Support\Repository\BaseModel;
use Paxifi\Tax\Calculator\Calculator;

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
        'total_items' => 'sometimes|required',
        'total_costs' => 'sometimes|required',
        'total_sales' => 'sometimes|required',
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

    /**
     * @return mixed
     */
    public function getTotalCosts()
    {
        return $this->total_costs;
    }

    /**
     * @return mixed
     */
    public function getTotalSales()
    {
        return $this->total_sales;
    }

    /**
     * @return mixed
     */
    public function getTotalTax()
    {
        return $this->total_tax;
    }

    /**
     * Add order item.
     *
     * @param array $item
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function addItem(array $item)
    {
        // Product id exists?
        $product = EloquentProductRepository::findOrFail($item['product_id']);

        // Product stock available?
        if ((int)$item['quantity'] > $product->inventory)
            throw new \InvalidArgumentException('Stock is not available.');

        // Total Items
        $this->total_items += $item['quantity'];

        // Total Costs
        $this->total_costs += $product->average_cost * $item['quantity'];

        // Total Tax
        $totalUnitPrice = $product->unit_price * $item['quantity'];
        $totalTax = Calculator::calculate($totalUnitPrice, $product->getTaxRate());
        $this->total_tax += $totalTax;

        // Total Sales
        $this->total_sales += $product->getTaxRate()->isIncludedInPrice() ? $totalUnitPrice : $totalUnitPrice + $totalTax;

        $this->products()->attach($product->id, array('quantity' => $item['quantity']));

        // Fires an event to update the inventory.
        static::$dispatcher->fire('paxifi.product.ordered', array($product, $item['quantity']));

        return $this;
    }

    /**
     * Set Paxifi's fee.
     *
     * @param double $commission
     *
     * @return $this
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * Set the driver's profit.
     *
     * @param double $profit
     *
     * @return $this
     */
    public function setProfit($profit)
    {
        $this->profit = $profit;

        return $this;
    }
}