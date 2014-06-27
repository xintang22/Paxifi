<?php namespace Paxifi\Tax\Repository;

use Paxifi\Support\Repository\BaseModel;

class OfficialTaxRate extends BaseModel implements TaxRateInterface
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'official_tax_rates';

    /**
     * Get tax amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getAttribute('amount');
    }

    /**
     * Get the amount as percentage.
     *
     * @return float
     */
    public function getAmountAsPercentage()
    {
        return $this->getAttribute('amount') * 100;
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
        $this->setAttribute('amount', $amount);

        return $this;
    }

    /**
     * Is included in price?
     *
     * @return Boolean
     */
    public function isIncludedInPrice()
    {
        return (boolean)$this->getAttribute('included_in_price');
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
        $this->setAttribute('included_in_price', (boolean)$includedInPrice);

        return $this;
    }
}