<?php

namespace App\Services\FeeCalculator;

interface FeeCalculatorInterface
{
    public function calculate(float $amount): float;
}
