<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingPlan extends Model
{
    protected $fillable = [
        'user_id', 'currency', 'capital', 'trader_type',
        'currency_pair', 'stop_loss_pips', 'take_profit_pips',
        'risk_per_trade', 'is_active',
    ];

    protected $casts = [
        'capital'        => 'decimal:2',
        'risk_per_trade' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRiskAmountAttribute(): float
    {
        return ($this->capital * $this->risk_per_trade) / 100;
    }

    public function getRrRatioAttribute(): float
    {
        if ($this->stop_loss_pips <= 0) return 0;
        return round($this->take_profit_pips / $this->stop_loss_pips, 1);
    }

    /**
     * Projected growth percentage over 30 days (used in notifications).
     * Delegates to TradingCalculatorService for accurate calculation.
     */
    public function getGrowthPctAttribute(): float
    {
        $dailyTargets = [
            'conservative' => 1.0,
            'moderate'     => 2.0,
            'aggressive'   => 3.0,
        ];

        $dailyTarget = $dailyTargets[$this->trader_type] ?? 2.0;

        // Compound growth: (1 + rate)^30 - 1
        return round((pow(1 + ($dailyTarget / 100), 30) - 1) * 100, 2);
    }
}
