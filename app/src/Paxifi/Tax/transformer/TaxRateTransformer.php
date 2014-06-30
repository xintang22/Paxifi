<?php namespace Paxifi\Tax\transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Tax\Repository\TaxRateInterface;

class TaxRateTransformer extends TransformerAbstract
{
    public function transform(TaxRateInterface $taxRate)
    {
        return array(
            'id' => $taxRate->id,
            'category' => $taxRate->category,
            'amount' => $taxRate->getAmount(),
            'percentage' => $taxRate->getAmountAsPercentage(),
            'included_in_price' => (boolean)$taxRate->isIncludedInPrice(),
        );
    }
} 