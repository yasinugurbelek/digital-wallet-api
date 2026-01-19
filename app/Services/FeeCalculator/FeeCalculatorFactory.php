<?php

namespace App\Services\FeeCalculator;

class FeeCalculatorFactory
{
    public static function make(float $amount): FeeCalculatorInterface
    {
        if ($amount <= 1000) {
            return new FixedFeeStrategy(2.0);
        } elseif ($amount <= 10000) {
            return new PercentageFeeStrategy(0.5);
        } else {
            return new TieredFeeStrategy();
        }
    }
}
