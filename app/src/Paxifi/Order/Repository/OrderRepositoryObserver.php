<?php namespace Paxifi\Order\Repository;

use Paxifi\Support\Commission\CalculatorInterface;

class OrderRepositoryObserver
{
    /**
     * @var \Paxifi\Support\Commission\CalculatorInterface
     */
    protected $commissionCalculator;

    function __construct(CalculatorInterface $commissionCalculator)
    {
        $this->commissionCalculator = $commissionCalculator;

    }

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param EloquentOrderRepository $order
     *
     * @return void
     */
    public function created(EloquentOrderRepository $order)
    {
        $order->setAttribute('commission', $this->commissionCalculator->calculateCommission($order));
        $order->setAttribute('profit', $this->commissionCalculator->calculateProfit($order));

        $order->save();
    }

}