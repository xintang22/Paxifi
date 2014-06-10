<?php namespace Paxifi\Support\Commission;

use Paxifi\Order\Repository\OrderRepositoryInterface;

interface CalculatorInterface
{
    /**
     * Calculate the store profit.
     *
     * @param \Paxifi\Order\Repository\OrderRepositoryInterface $order
     *
     * @return double
     */
    public function calculateProfit(OrderRepositoryInterface $order);

    /**
     * Calculate Paxifi's commission.
     *
     * @param \Paxifi\Order\Repository\OrderRepositoryInterface $order
     *
     * @return double
     */
    public function calculateCommission(OrderRepositoryInterface $order);

    /**
     * Retrieve the commission rate.
     *
     * @return double
     */
    public function getCommissionRate();

    /**
     * Set the commission rate.
     *
     * @param double $rate
     *
     * @return $this
     */
    public function setCommissionRate($rate);
} 