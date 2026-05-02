<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TradingPlan;
use App\Models\JournalEntry;
use App\Models\Challenge;
use App\Models\ChallengeDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@cuanhunters.com')->first();
        if (!$user) {
            $user = User::create([
                'name'            => 'Demo Trader',
                'email'           => 'demo@cuanhunters.com',
                'password'        => Hash::make('password'),
                'currency'        => 'IDR',
                'default_capital' => 5000000,
                'trader_type'     => 'moderate',
                'timezone'        => 'Asia/Jakarta',
            ]);
        }

        // Clean up existing dummy data for this user to avoid duplication if run multiple times
        TradingPlan::where('user_id', $user->id)->delete();
        JournalEntry::where('user_id', $user->id)->delete();
        $challenges = Challenge::where('user_id', $user->id)->get();
        foreach($challenges as $c) {
            ChallengeDay::where('challenge_id', $c->id)->delete();
            $c->delete();
        }

        // 1. Trading Plans
        TradingPlan::create([
            'user_id' => $user->id,
            'currency' => 'IDR',
            'capital' => 10000000,
            'trader_type' => 'moderate',
            'currency_pair' => 'EUR/USD',
            'stop_loss_pips' => 20,
            'take_profit_pips' => 40,
            'risk_per_trade' => 1.5,
            'is_active' => true,
        ]);

        TradingPlan::create([
            'user_id' => $user->id,
            'currency' => 'USD',
            'capital' => 500,
            'trader_type' => 'aggressive',
            'currency_pair' => 'XAU/USD',
            'stop_loss_pips' => 30,
            'take_profit_pips' => 90,
            'risk_per_trade' => 2.0,
            'is_active' => false,
        ]);

        // 2. Journal Entries
        $pairs = ['EUR/USD', 'GBP/USD', 'XAU/USD', 'USD/JPY'];
        $results = ['win', 'loss', 'breakeven'];
        $emotions = [4, 5, 3, 2];

        for ($i = 0; $i < 15; $i++) {
            $result = $results[array_rand($results)];
            $profitLoss = ($result === 'win') ? rand(150000, 600000) : (($result === 'loss') ? rand(-400000, -100000) : 0);
            
            JournalEntry::create([
                'user_id' => $user->id,
                'entry_date' => Carbon::now()->subDays(20 - $i),
                'currency_pair' => $pairs[array_rand($pairs)],
                'trade_type' => rand(0, 1) ? 'buy' : 'sell',
                'profit_loss' => $profitLoss,
                'result' => $result,
                'analysis' => 'Analisa teknikal pada timeframe H1 dengan konfirmasi candlestick pattern.',
                'lesson_learned' => 'Penting untuk menjaga psikologi dan tidak overtrade.',
                'emotion_score' => $emotions[array_rand($emotions)],
                'market_condition' => 'Trending',
            ]);
        }

        // 3. Challenge
        $challenge = Challenge::create([
            'user_id' => $user->id,
            'initial_capital' => 5000000,
            'target_capital' => 10000000,
            'total_profit' => 1500000,
            'current_day' => 6,
            'progress_percent' => 30.0,
            'status' => 'active',
            'started_at' => Carbon::now()->subDays(10),
        ]);

        $dailyTarget = 200000;
        for ($d = 1; $d <= 30; $d++) {
            $status = ($d < 6) ? 'completed' : (($d == 6) ? 'pending' : 'pending');
            $actualResult = ($d < 6) ? $dailyTarget + rand(-20000, 50000) : 0;

            ChallengeDay::create([
                'challenge_id' => $challenge->id,
                'day_number' => $d,
                'start_capital' => 5000000 + (($d - 1) * $dailyTarget),
                'target_profit' => $dailyTarget,
                'actual_result' => $actualResult,
                'notes' => ($d < 6) ? 'Berhasil mencapai target harian ke-' . $d : null,
                'status' => $status,
                'completed_at' => ($d < 6) ? Carbon::now()->subDays(10 - $d) : null,
            ]);
        }
    }
}
