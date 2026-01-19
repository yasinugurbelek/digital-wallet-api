<?php

namespace App\Services\FeeCalculator;

class PercentageFeeStrategy implements FeeCalculatorInterface
{
    public function __construct(private float $percentage = 0.5)
    {
    }

    public function calculate(float $amount): float
    {
        return round($amount * ($this->percentage / 100), 2);
    }
}
