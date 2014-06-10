<?php namespace Paxifi\Order\Repository;

interface OrderRepositoryInterface
{
    public function getTotalCosts();

    public function getTotalSales();
}