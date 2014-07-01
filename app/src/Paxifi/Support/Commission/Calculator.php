<?php namespace Paxifi\Support\Commission;

use Paxifi\Order\Repository\OrderRepositoryInterface;

class Calculator implements CalculatorInterface
{
    protected $commissionRate;

    function __construct($commissionRate)
    {
        $this->commissionRate = $commissionRate;
    }

    /**
     * Retrieve the commission rate.
     *
     * @return double
     */
    public function getCommissionRate()
    {
        return $this->commissionRate;
    }

    /**
     * Set the commission rate.
     *
     * @param double $rate
     *
     * @return $this
     */
    public function setCommissionRate($rate)
    {
        $this->commissionRate = $rate;

        return $this;
    }

    /**
     * Calculate the store profit.
     *
     * @param \Paxifi\Order\Repository\OrderRepositoryInterface $order
     *
     * @return mixed
     */
    public function calculateProfit(OrderRepositoryInterface $order)
    {
        $sales = $order->getTotalSales();
        $costs = $order->getTotalCosts();
        $tax = $order->getTotalTax();

        return $sales - $costs - $tax - $this->calculateCommission($order);
    }

    /**
     * Calculate Paxifi's commission.
     *
     * @param \Paxifi\Order\Repository\OrderRepositoryInterface $order
     *
     * @return double
     */
    public function calculateCommission(OrderRepositoryInterface $order)
    {
        return $order->getTotalSales() * $this->commissionRate;
    }
}