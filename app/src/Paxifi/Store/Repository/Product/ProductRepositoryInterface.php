<?php namespace Paxifi\Store\Repository\Product;

interface ProductRepositoryInterface
{

    /**
     * Decrease the product inventory.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function updateInventory($amount = 1);

} 