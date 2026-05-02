<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'user_id', 'entry_date', 'currency_pair', 'trade_type',
        'profit_loss', 'result', 'analysis', 'lesson_learned',
        'emotion_score', 'market_condition',
    ];

    protected $casts = [
        'entry_date'  => 'date',
        'profit_loss' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWins($query)
    {
        return $query->where('result', 'win');
    }

    public function scopeLosses($query)
    {
        return $query->where('result', 'loss');
    }
}
