<?php namespace Paxifi\Tax\Repository;

class TaxRate implements TaxRateInterface
{
    /**
     * @var float array
     */
    protected $amount;

    /**
     * @var boolean
     */
    protected $includedInPrice;

    /**
     * @param float $amount
     * @param boolean $includedInPrice
     */
    function __construct($amount, $includedInPrice)
    {
        $this->amount = $amount;
        $this->includedInPrice = $includedInPrice;
    }

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
        return $this->includedInPrice;
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
        $this->includedInPrice = (Boolean)$includedInPrice;

        return $this;
    }
}