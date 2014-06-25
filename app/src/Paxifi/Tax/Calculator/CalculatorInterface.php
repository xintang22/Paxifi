<?php namespace Paxifi\Tax\Calculator;

use Paxifi\Tax\Repository\TaxRateInterface;

interface CalculatorInterface
{
    public function calculate($base, TaxRateInterface $rate);
} 