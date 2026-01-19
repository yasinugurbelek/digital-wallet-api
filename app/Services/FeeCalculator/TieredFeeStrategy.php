<?php

namespace App\Services\FeeCalculator;

class TieredFeeStrategy implements FeeCalculatorInterface
{
    public function calculate(float $amount): float
    {
        if ($amount <= 1000) {
            return 2.0;
        }
        
        $baseFee = 2.0;
        $remainder = $amount - 1000;
        $percentageFee = round($remainder * 0.003, 2);
        
        return $baseFee + $percentageFee;
    }
}
