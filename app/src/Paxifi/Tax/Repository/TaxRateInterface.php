<?php namespace Paxifi\Tax\Repository;

interface TaxRateInterface
{
    /**
     * Get tax amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get the amount as percentage.
     *
     * @return float
     */
    public function getAmountAsPercentage();

    /**
     * Set tax amount.
     *
     * @param float $amount
     */
    public function setAmount($amount);

    /**
     * Is included in price?
     *
     * @return Boolean
     */
    public function isIncludedInPrice();

    /**
     * Set as included in price or not.
     *
     * @param Boolean $includedInPrice
     */
    public function setIncludedInPrice($includedInPrice);

} 