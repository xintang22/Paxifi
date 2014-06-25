<?php namespace Paxifi\Tax\Repository;

use Paxifi\Support\Repository\BaseModel;

class TaxRate extends BaseModel implements TaxRateInterface
{

    protected $table = 'tax_rates';

    /**
     * Get tax amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the amount as percentage.
     *
     * @return float
     */
    public function getAmountAsPercentage()
    {
        return $this->getAmount() * 100;
    }

    /**
     * Set tax amount.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Is included in price?
     *
     * @return Boolean
     */
    public function isIncludedInPrice()
    {
        return $this->include_in_price;
    }

    /**
     * Set as included in price or not.
     *
     * @param Boolean $includedInPrice
     *
     * @return $this
     */
    public function setIncludedInPrice($includedInPrice)
    {
        $this->included_in_price = (Boolean)$includedInPrice;

        return $this;
    }
}