<?php

namespace App\Services;

use App\Models\TradingPlan;

class TradingCalculatorService
{
    private array $traderConfig = [
        'conservative' => ['dailyTarget' => 1.0, 'maxRisk' => 1.0, 'maxTrades' => 2],
        'moderate'     => ['dailyTarget' => 2.0, 'maxRisk' => 2.0, 'maxTrades' => 3],
        'aggressive'   => ['dailyTarget' => 3.0, 'maxRisk' => 3.0, 'maxTrades' => 5],
    ];

    /**
     * Pip value per STANDARD LOT (1.0 lot) in USD
     *
     * Forex pairs: 1 pip = 0.0001 move × 100,000 units
     * JPY pairs:   1 pip = 0.01 move × 100,000 units / rate
     * XAU/USD:     1 pip = $0.01 move × 100 oz = $1.00
     * BTC/USD:     1 pip = $1.00 move × 1 BTC  = $1.00
     */
    private array $pipValues = [
        'EUR/USD' => 10,    'GBP/USD' => 10,    'USD/JPY' => 9.09,
        'AUD/USD' => 10,    'USD/CAD' => 7.69,  'EUR/GBP' => 13,
        'XAU/USD' => 1,     'BTC/USD' => 1,     'NZD/USD' => 10,
        'USD/CHF' => 9.09,  'EUR/JPY' => 9.09,  'GBP/JPY' => 9.09,
    ];

    /**
     * Exchange rates to USD for lot size conversion
     * IDR: approximate rate, USC: 100 USC = 1 USD
     */
    private array $exchangeRateToUSD = [
        'USD' => 1,
        'IDR' => 1 / 15500,
        'USC' => 1 / 100,
    ];

    public function generate(TradingPlan $plan): array
    {
        // For preview (plan ID = 0 or null), don't cache
        if (empty($plan->id)) {
            return $this->calculatePlan($plan);
        }
        
        // Cache key based on plan ID and last update timestamp
        $cacheKey = "trading_plan:{$plan->id}:{$plan->updated_at->timestamp}";
        
        // Cache for 1 hour (3600 seconds)
        $cacheDuration = 3600;
        
        return cache()->remember($cacheKey, $cacheDuration, function () use ($plan) {
            return $this->calculatePlan($plan);
        });
    }
    
    /**
     * Calculate trading plan without caching
     */
    private function calculatePlan(TradingPlan $plan): array
    {
        $config  = $this->traderConfig[$plan->trader_type];
        $capital = (float) $plan->capital;
        $days    = [];

        for ($day = 1; $day <= 30; $day++) {
            $dailyProfit = $capital * ($config['dailyTarget'] / 100);
            $riskAmount  = $capital * ($config['maxRisk'] / 100);
            $lotSize     = $this->calculateLotSize($riskAmount, $plan->stop_loss_pips, $plan->currency_pair, $plan->currency);

            $days[] = [
                'day'               => $day,
                'capital'           => round($capital, 2),
                'target_profit'     => round($dailyProfit, 2),
                'risk_amount'       => round($riskAmount, 2),
                'lot_size'          => $lotSize,
                'capital_formatted' => $this->formatCurrency($capital, $plan->currency),
                'target_formatted'  => $this->formatCurrency($dailyProfit, $plan->currency),
                'profit_formatted'  => $this->formatCurrency($dailyProfit, $plan->currency),
                'risk_formatted'    => $this->formatCurrency($riskAmount, $plan->currency),
            ];

            $capital += $dailyProfit; // Compound
        }

        $initial = (float) $plan->capital;

        return [
            'days'          => $days,
            'final_capital' => round($capital, 2),
            'total_profit'  => round($capital - $initial, 2),
            'growth_pct'    => round((($capital / $initial) - 1) * 100, 2),
            'config'        => $config,
        ];
    }

    /**
     * Calculate lot size with currency conversion
     *
     * @param float  $riskAmount Risk amount in the user's currency
     * @param int    $slPips     Stop loss in pips
     * @param string $pair       Currency pair (e.g. EUR/USD)
     * @param string $currency   User's account currency (USD, IDR, USC)
     */
    public function calculateLotSize(float $riskAmount, int $slPips, string $pair, string $currency = 'USD'): float
    {
        $pipValue = $this->pipValues[$pair] ?? 10;
        if ($slPips <= 0 || $pipValue <= 0) return 0;

        // Convert risk amount to USD before calculating lot size
        $rate = $this->exchangeRateToUSD[$currency] ?? 1;
        $riskInUSD = $riskAmount * $rate;

        // Raw lot size in standard lots
        $rawLot = $riskInUSD / ($slPips * $pipValue);

        // Floor to nearest 0.01 (micro lot) — never exceed planned risk
        $lot = floor($rawLot * 100) / 100;

        // Minimum tradeable lot is 0.01 (micro lot)
        if ($lot < 0.01 && $rawLot > 0) {
            return 0.01;
        }

        return $lot;
    }

    public function formatCurrency(float $amount, string $currency): string
    {
        if ($currency === 'USD') {
            return '$' . number_format($amount, 2);
        }
        if ($currency === 'USC') {
            return number_format($amount, 0, ',', '.') . ' ¢';
        }
        // IDR
        if ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1) . 'jt';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get minimum capital requirement based on currency
     */
    public function getMinCapital(string $currency): float
    {
        return match ($currency) {
            'USD' => 50,
            'USC' => 5000,  // 5000 USC = $50
            default => 100000, // IDR
        };
    }

    public function getTraderConfigs(): array
    {
        return $this->traderConfig;
    }

    public function getCurrencyPairs(): array
    {
        return array_keys($this->pipValues);
    }

    /**
     * Clear cached calculation for a specific trading plan
     */
    public function clearCache(TradingPlan $plan): void
    {
        // Clear cache for current timestamp version
        $cacheKey = "trading_plan:{$plan->id}:{$plan->updated_at->timestamp}";
        cache()->forget($cacheKey);
    }

    /**
     * Get cache statistics for debugging/monitoring
     */
    public function getCacheStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'enabled' => config('cache.enabled', true),
        ];
    }
}
