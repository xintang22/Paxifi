<?php namespace Paxifi\Tax\Calculator;

use Paxifi\Tax\Repository\TaxRateInterface;

interface CalculatorInterface
{
    public static function calculate($base, TaxRateInterface $rate);
} 