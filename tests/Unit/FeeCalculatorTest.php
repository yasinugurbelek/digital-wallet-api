<?php

namespace Tests\Unit;

use App\Services\FeeCalculator\{FixedFeeStrategy, PercentageFeeStrategy, TieredFeeStrategy, FeeCalculatorFactory};
use PHPUnit\Framework\TestCase;

class FeeCalculatorTest extends TestCase
{
    public function test_fixed_fee_strategy()
    {
        $calculator = new FixedFeeStrategy(2.0);
        $this->assertEquals(2.0, $calculator->calculate(500));
        $this->assertEquals(2.0, $calculator->calculate(1000));
    }

    public function test_percentage_fee_strategy()
    {
        $calculator = new PercentageFeeStrategy(0.5);
        $this->assertEquals(2.5, $calculator->calculate(500));
        $this->assertEquals(5.0, $calculator->calculate(1000));
    }

    public function test_tiered_fee_strategy()
    {
        $calculator = new TieredFeeStrategy();
        $this->assertEquals(2.0, $calculator->calculate(1000));
        $this->assertEquals(5.0, $calculator->calculate(2000)); // 2 + (1000 * 0.003)
    }

    public function test_fee_calculator_factory()
    {
        $calc1 = FeeCalculatorFactory::make(500);
        $this->assertInstanceOf(FixedFeeStrategy::class, $calc1);

        $calc2 = FeeCalculatorFactory::make(5000);
        $this->assertInstanceOf(PercentageFeeStrategy::class, $calc2);

        $calc3 = FeeCalculatorFactory::make(15000);
        $this->assertInstanceOf(TieredFeeStrategy::class, $calc3);
    }
}
