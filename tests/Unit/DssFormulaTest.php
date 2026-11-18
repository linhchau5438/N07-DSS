<?php

namespace Tests\Unit;

use App\Services\DemandForecastService;
use App\Services\ReorderCalculator;
use PHPUnit\Framework\TestCase;

class DssFormulaTest extends TestCase
{
    public function test_document_examples_and_pack_rounding(): void
    {
        $calculator = new ReorderCalculator;
        $this->assertEqualsWithDelta(12.57142857, (new DemandForecastService)->predict([10, 12, 15, 8, 14, 16, 13], 'ma'), 0.00001);
        $result = $calculator->calculate([13, 13, 13, 13, 13, 13, 13], 4, 7, 20, 70, 0);
        $this->assertEquals(72, $result['rop']);
        $this->assertSame(93, $result['quantity']);
        $this->assertSame(96, $calculator->calculate([13], 4, 7, 20, 70, 0, 12)['quantity']);
        $this->assertSame(0, $calculator->calculate([13], 4, 7, 20, 80, 0)['quantity']);
        $this->assertSame('NORMAL', $calculator->calculate([13], 4, 7, 20, 70, 10)['stock_status']);
    }

    public function test_forecast_compares_same_holdout_and_falls_back_to_ma(): void
    {
        $service = new DemandForecastService;
        $trend = $service->evaluate(range(1, 37), 30);
        $this->assertSame('linear', $trend['selected']);
        $this->assertEqualsWithDelta(0, $trend['metrics']['linear']['mae'], 0.00001);
        $this->assertEqualsWithDelta(38, $trend['metrics']['linear']['forecast'], 0.00001);
        $this->assertSame('ma', $service->evaluate(array_fill(0, 37, 10), 30)['selected']);
        $this->assertFalse($service->evaluate([1, 2, 3], 30)['sufficient']);
        $this->assertNull($service->evaluate([1, 2, 3], 30)['metrics']['linear']['mae']);
    }
}
