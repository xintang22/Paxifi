<?php namespace Paxifi\Sales\Repository;

use Illuminate\Support\Contracts\ArrayableInterface;

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
    protected $buyerComment;
    protected $createdAt;

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
        $this->buyerFeedback = $order->feedback;
        $this->buyerComment = $order->comment;
        $this->createdAt = (string)$order->created_at;
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
        return array(
            'id' => $this->id,
            'total_items' => $this->totalItems,
            'total_costs' => $this->totalCosts,
            'total_sales' => $this->totalSales,
            'profit' => $this->profit,
            'commission' => $this->commission,
            // 'payment_method' => $this->paymentMethod,
            'products' => $this->products,
            'buyer' => array(
                'email' => $this->buyerEmail,
                'feedback' => $this->buyerFeedback,
                'comment' => $this->buyerComment,
            ),
            'status' => $this->status,
            'created_at' => (string)$this->createdAt,
        );
    }

    protected function formatProducts($order)
    {
        return $order->products()->get()->map(function ($product) {
            return array(
                'id' => $product->id,
                'quantity' => $product->pivot->quantity,
            );
        });
    }
} 