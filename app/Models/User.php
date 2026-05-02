<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'currency', 'default_capital', 'trader_type', 'timezone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'default_capital'   => 'decimal:2',
        ];
    }

    public function tradingPlans(): HasMany
    {
        return $this->hasMany(TradingPlan::class);
    }

    public function activeTradingPlan(): ?TradingPlan
    {
        return $this->tradingPlans()->where('is_active', true)->first();
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    public function activeChallenge(): ?Challenge
    {
        return $this->challenges()->where('status', 'active')->first();
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}
