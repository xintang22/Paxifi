<?php namespace Paxifi\Tax;

interface TaxableInterface
{

    /**
     * Get the product's tax rate.
     *
     * @return \Paxifi\Tax\Repository\TaxRateInterface
     */
    public function getTaxRate();

}