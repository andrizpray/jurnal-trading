<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeDay extends Model
{
    protected $fillable = [
        'challenge_id', 'day_number', 'start_capital',
        'target_profit', 'actual_result', 'notes',
        'status', 'completed_at',
    ];

    protected $casts = [
        'start_capital'  => 'decimal:2',
        'target_profit'  => 'decimal:2',
        'actual_result'  => 'decimal:2',
        'completed_at'   => 'date',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function getIsCurrentAttribute(): bool
    {
        return $this->day_number === $this->challenge->current_day
            && $this->status === 'pending';
    }
}
