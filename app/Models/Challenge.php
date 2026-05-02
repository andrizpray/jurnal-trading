<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    protected $fillable = [
        'user_id', 'initial_capital', 'target_capital',
        'total_profit', 'current_day', 'progress_percent',
        'status', 'started_at',
    ];

    protected $casts = [
        'initial_capital' => 'decimal:2',
        'target_capital'  => 'decimal:2',
        'total_profit'    => 'decimal:2',
        'started_at'      => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ChallengeDay::class)->orderBy('day_number');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getCurrentCapitalAttribute(): float
    {
        return (float) $this->initial_capital + (float) $this->total_profit;
    }

    public function getGrowthPercentAttribute(): float
    {
        if ($this->initial_capital <= 0) return 0;
        return round((($this->current_capital / $this->initial_capital) - 1) * 100, 2);
    }
}
