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
     * Order - Payment one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment()
    {
        return $this->hasOne('Paxifi\Payment\Repository\EloquentPaymentRepository', 'order_id', 'id');
    }

    /***
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

        // Fires an event to notification the driver that the product is in low inventory.
        if (EloquentProductRepository::find($item['product_id'])->inventory <= 5) {
            static::$dispatcher->fire('paxifi.notifications.stock', array($product));
        }

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


    /**
     * Set buyer email for send the invoice.
     *
     * @param email $email
     *
     * @return $this
     */
    public function setBuyerEmail($email = NULL)
    {
        $this->buyer_email = $email;

        return $this;
    }

    /**
     * Format response order update time in (F, d, Y)
     *
     * @param $updated_at
     * @return bool|string
     */
    public function getUpdatedAtAttribute($updated_at)
    {
        return date('F d, Y' ,strtotime($updated_at));
    }

    /**
     * Get the products collections related to the order
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function OrderProducts()
    {
        return $this->products()->wherePivot('order_id', '=', $this->id)->get();
    }

    /**
     * Get the driver which the order belongs to.
     *
     * @return mixed
     */
    public function OrderDriver()
    {
        return $this->products()->first()->driver()->get()->first();
    }
}