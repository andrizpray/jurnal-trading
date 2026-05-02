<?php

namespace Tests\Unit;

use App\Models\TradingPlan;
use App\Services\TradingCalculatorService;
use Tests\TestCase;
use Carbon\Carbon;

class TradingCalculatorServiceTest extends TestCase
{
    private TradingCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new TradingCalculatorService();
    }

    public function test_generate_returns_30_days(): void
    {
        $plan = $this->makePlan();
        $result = $this->calculator->generate($plan);

        $this->assertArrayHasKey('days', $result);
        $this->assertCount(30, $result['days']);
    }

    public function test_generate_compounds_capital_daily(): void
    {
        $plan = $this->makePlan('moderate', 1000000);
        $result = $this->calculator->generate($plan);

        $day1Capital = $result['days'][0]['capital'];
        $day2Capital = $result['days'][1]['capital'];

        // Day 2 capital should be Day 1 capital + Day 1 target profit
        $expectedDay2 = $day1Capital + $result['days'][0]['target_profit'];
        $this->assertEqualsWithDelta($expectedDay2, $day2Capital, 0.01);
    }

    public function test_generate_growth_pct_is_positive(): void
    {
        $plan = $this->makePlan('aggressive', 5000000);
        $result = $this->calculator->generate($plan);

        $this->assertGreaterThan(0, $result['growth_pct']);
        $this->assertGreaterThan(0, $result['total_profit']);
    }

    public function test_moderate_daily_target_is_2_percent(): void
    {
        $plan = $this->makePlan('moderate', 1000000);
        $result = $this->calculator->generate($plan);

        // Day 1 target should be 2% of 1,000,000 = 20,000
        $this->assertEqualsWithDelta(20000, $result['days'][0]['target_profit'], 1);
    }

    public function test_conservative_daily_target_is_1_percent(): void
    {
        $plan = $this->makePlan('conservative', 1000000);
        $result = $this->calculator->generate($plan);

        $this->assertEqualsWithDelta(10000, $result['days'][0]['target_profit'], 1);
    }

    public function test_aggressive_daily_target_is_3_percent(): void
    {
        $plan = $this->makePlan('aggressive', 1000000);
        $result = $this->calculator->generate($plan);

        $this->assertEqualsWithDelta(30000, $result['days'][0]['target_profit'], 1);
    }

    public function test_calculate_lot_size(): void
    {
        // Risk: $100,000 USD | SL: 20 pips | EUR/USD (pip=$10)
        // raw = 100000 / (20 * 10) = 500.00 → floor to 0.01 step = 500.00
        $lotSize = $this->calculator->calculateLotSize(100000, 20, 'EUR/USD', 'USD');
        $this->assertEquals(500, $lotSize);
    }

    public function test_calculate_lot_size_returns_zero_for_invalid_sl(): void
    {
        $lotSize = $this->calculator->calculateLotSize(100000, 0, 'EUR/USD', 'USD');
        $this->assertEquals(0, $lotSize);
    }

    public function test_calculate_lot_size_converts_idr_to_usd(): void
    {
        // Risk: 1,550,000 IDR ≈ $100 USD (rate 1/15500)
        // raw = 100 / (20 * 10) = 0.50 → floor = 0.50
        $lotSize = $this->calculator->calculateLotSize(1550000, 20, 'EUR/USD', 'IDR');
        $this->assertEquals(0.50, $lotSize);
    }

    public function test_calculate_lot_size_converts_usc_to_usd(): void
    {
        // Risk: 10000 USC = $100 USD (100 USC = 1 USD)
        // raw = 100 / (20 * 10) = 0.50 → floor = 0.50
        $lotSize = $this->calculator->calculateLotSize(10000, 20, 'EUR/USD', 'USC');
        $this->assertEquals(0.50, $lotSize);
    }

    public function test_lot_size_minimum_is_001(): void
    {
        // Very small risk: $1 USD, 50 pips SL on EUR/USD
        // raw = 1 / (50 * 10) = 0.002 → below 0.01 → return min 0.01
        $lotSize = $this->calculator->calculateLotSize(1, 50, 'EUR/USD', 'USD');
        $this->assertEquals(0.01, $lotSize);
    }

    public function test_lot_size_floors_to_nearest_001(): void
    {
        // Risk: $15 USD, 10 pips SL on EUR/USD
        // raw = 15 / (10 * 10) = 0.15 → floor = 0.15
        $lotSize = $this->calculator->calculateLotSize(15, 10, 'EUR/USD', 'USD');
        $this->assertEquals(0.15, $lotSize);

        // Risk: $17 USD, 10 pips SL on EUR/USD
        // raw = 17 / (10 * 10) = 0.17 → floor = 0.17
        $lotSize = $this->calculator->calculateLotSize(17, 10, 'EUR/USD', 'USD');
        $this->assertEquals(0.17, $lotSize);
    }

    public function test_format_currency_idr(): void
    {
        $formatted = $this->calculator->formatCurrency(1500000, 'IDR');
        $this->assertStringContainsString('Rp', $formatted);
        $this->assertStringContainsString('1.5', $formatted);
    }

    public function test_format_currency_usd(): void
    {
        $formatted = $this->calculator->formatCurrency(1500.50, 'USD');
        $this->assertStringStartsWith('$', $formatted);
        $this->assertStringContainsString('1,500.50', $formatted);
    }

    public function test_format_currency_usc(): void
    {
        $formatted = $this->calculator->formatCurrency(5000, 'USC');
        $this->assertStringContainsString('5.000', $formatted);
        $this->assertStringContainsString('¢', $formatted);
    }

    public function test_get_min_capital_usd(): void
    {
        $this->assertEquals(50, $this->calculator->getMinCapital('USD'));
    }

    public function test_get_min_capital_idr(): void
    {
        $this->assertEquals(100000, $this->calculator->getMinCapital('IDR'));
    }

    public function test_get_min_capital_usc(): void
    {
        $this->assertEquals(5000, $this->calculator->getMinCapital('USC'));
    }

    public function test_get_currency_pairs_not_empty(): void
    {
        $pairs = $this->calculator->getCurrencyPairs();
        $this->assertNotEmpty($pairs);
        $this->assertContains('EUR/USD', $pairs);
        $this->assertContains('XAU/USD', $pairs);
        $this->assertContains('BTC/USD', $pairs);
    }

    public function test_get_trader_configs_has_all_types(): void
    {
        $configs = $this->calculator->getTraderConfigs();
        $this->assertArrayHasKey('conservative', $configs);
        $this->assertArrayHasKey('moderate', $configs);
        $this->assertArrayHasKey('aggressive', $configs);
    }

    public function test_plan_with_id_zero_skips_cache(): void
    {
        // Plan ID = 0 should not attempt caching
        $plan = $this->makePlan('moderate', 1000000);
        $plan->id = 0;

        // Should not throw, cache()->remember() is skipped
        $result = $this->calculator->generate($plan);
        $this->assertNotEmpty($result['days']);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function makePlan(
        string $traderType = 'moderate',
        float $capital = 1000000,
        string $pair = 'EUR/USD',
        int $sl = 20,
        int $tp = 40
    ): TradingPlan {
        $plan = new TradingPlan();
        $plan->id = 0; // skip cache
        $plan->trader_type = $traderType;
        $plan->capital = $capital;
        $plan->currency = 'IDR';
        $plan->currency_pair = $pair;
        $plan->stop_loss_pips = $sl;
        $plan->take_profit_pips = $tp;
        $plan->risk_per_trade = 2.0;
        $plan->updated_at = Carbon::now();
        return $plan;
    }
}
