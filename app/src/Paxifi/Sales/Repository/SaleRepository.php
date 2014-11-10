<?php namespace Paxifi\Sales\Repository;

use Illuminate\Support\Contracts\ArrayableInterface;
use Paxifi\Order\Repository\EloquentOrderRepository;

class SaleRepository implements ArrayableInterface
{
    protected $id;
    protected $totalItems;
    protected $totalCosts;
    protected $totalSales;
    protected $totalTax;
    protected $profit;
    protected $commission;
    protected $paymentMethod;
    protected $status;
    protected $products = [];
    protected $buyerEmail;
    protected $buyerFeedback;
    protected $createdAt;
    protected $updatedAt;

    function __construct($order)
    {
        $this->id = $order->id;
        $this->totalItems = $order->total_items;
        $this->totalCosts = $order->total_costs;
        $this->totalSales = $order->total_sales;
        $this->totalTax = $order->total_tax;
        $this->profit = $order->profit;
        $this->commission = $order->commission;
        $this->paymentMethod = 'paypal';
        $this->status = $order->status;
        $this->buyerEmail = $order->buyer_email;
        $this->buyerFeedback = $order->payment->feedback;
        $this->createdAt = $order->created_at;
        $this->updatedAt = $order->updated_at;
        $this->products = $this->formatProducts($order);
    }

    /**
     * @return mixed
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @return mixed
     */
    public function getTotalCosts()
    {
        return $this->totalCosts;
    }

    /**
     * @return mixed
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @return mixed
     */
    public function getProfit()
    {
        return $this->profit;
    }

    /**
     * @return mixed
     */
    public function getTotalSales()
    {
        return $this->totalSales;
    }

    /**
     * @return mixed
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * Get the sale as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        $feedback = EloquentOrderRepository::find($this->id)->payment->feedback;

        return array(
            'id' => $this->id,
            'total_items' => $this->totalItems,
            'total_costs' => $this->totalCosts,
            'total_sales' => $this->totalSales,
            'total_tax' => $this->totalTax,
            'profit' => $this->profit,
            'commission' => $this->commission,
            // 'payment_method' => $this->paymentMethod,
            'products' => $this->products,
            'buyer' => array(
                'email' => $this->buyerEmail,
                'feedback' => $this->buyerFeedback,
            ),
            'status' => $this->status,
            'created_at' => (string)$this->createdAt,
            'updated_at' => (string)$this->updatedAt,
            'created_at_month' => $this->createdAt->month,
            'created_at_year' => $this->createdAt->year,
        );
    }

    protected function formatProducts($order)
    {
        return $order->products()->get()->map(function ($product) {
            return array(
                'id' => $product->id,
                'name' => $product->name,
                'unit_price' => $product->unit_price,
                'quantity' => $product->pivot->quantity,
                'updated_at' => $product->updated_at->format('H:s m/d/Y'),
            );
        });
    }
} 