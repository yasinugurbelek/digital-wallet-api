<?php

namespace App\Services\FeeCalculator;

class FixedFeeStrategy implements FeeCalculatorInterface
{
    public function __construct(private float $fee = 2.0)
    {
    }

    public function calculate(float $amount): float
    {
        return $this->fee;
    }
}
