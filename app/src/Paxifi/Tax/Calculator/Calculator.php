<?php namespace Paxifi\Tax\Calculator;

use Paxifi\Tax\Repository\TaxRateInterface;

class Calculator implements CalculatorInterface
{
    public function calculate($base, TaxRateInterface $rate)
    {
        if ($rate->isIncludedInPrice()) {
            return intval($base - round($base / (1 + $rate->getAmount())));
        }

        return intval(round($base * $rate->getAmount()));
    }
}