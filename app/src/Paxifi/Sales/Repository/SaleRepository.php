<?php namespace Paxifi\Sales\Repository;

use Illuminate\Support\Contracts\ArrayableInterface;
use Paxifi\Problem\Repository\EloquentProblemRepository;
use Paxifi\Tax\Calculator\Calculator;

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
        return array(
            'id' => $this->id,
            'total_items' => $this->totalItems,
            'total_costs' => $this->totalCosts,
            'total_sales' => $this->totalSales,
            'total_tax' => $this->totalTax,
            'profit' => $this->profit,
            'commission' => $this->commission,
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

    /**
     * Format the products response.
     *
     * @param $order
     *
     * @return mixed
     */
    protected function formatProducts($order)
    {
        return $order->products->map(function ($product) use($order) {
            $salesInfo = $this->getProductSaleInfo($product);

            return array(
                'id' => $product->id,
                'name' => $product->name,
                'unit_price' => $product->unit_price,
                'quantity' => $product->pivot->quantity,
                'cost' => $product->average_cost,
                'profit' => $salesInfo->productProfit,
                'category' => $product->category->name,
                'issues' => $this->getProductProblemsInSale($product, $order),
                'updated_at' => $product->updated_at->format('H:s m/d/Y'),
            );
        });
    }

    /**
     * Get product information in one specific sales.
     *
     * @param $product
     *
     * @return array
     */
    protected function getProductSaleInfo($product) {

        $info = new \stdClass();

        $info->productUnitPrice = $product->unit_price * $product->pivot->quantity;
        $info->productCosts     = $product->average_cost * $product->pivot->quantity;

        if ($product->driver->tax_enabled) {
            $info->productTax = Calculator::calculate($info->productUnitPrice, $product->getTaxRate());
        } else {
            $info->productTax = 0;
        }
        $info->productSales = $product->getTaxRate()->isIncludedInPrice() ? $info->productUnitPrice : $info->productUnitPrice + $info->productTax;
        $info->productCommission = $info->productUnitPrice * $product->driver->getCommissionRate();
        $info->productProfit = $info->productSales - $info->productCosts - $info->productTax - $info->productCommission;

        return $info;
    }

    /**
     * Get related product problem in specific sale.
     *
     * @param $product
     * @param $order
     *
     * @return mixed
     */
    protected function getProductProblemsInSale($product, $order)
    {
        return EloquentProblemRepository::getRelatedProblems($order->payment->id, $product->id)->toArray();
    }
} 