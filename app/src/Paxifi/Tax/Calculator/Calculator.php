<?php namespace Paxifi\Tax\Calculator;

use Paxifi\Tax\Repository\TaxRateInterface;

class Calculator implements CalculatorInterface
{
    public static function calculate($base, TaxRateInterface $rate)
    {
        if ($rate->isIncludedInPrice()) {
            return $base - ($base / (1 + $rate->getAmount()));
        }

        return $base * $rate->getAmount();
    }
}