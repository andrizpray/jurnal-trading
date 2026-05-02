<?php

namespace App\Services;

use App\Models\User;
use App\Models\JournalEntry;
use App\Models\Challenge;
use App\Models\TradingPlan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get comprehensive analytics for dashboard
     */
    public function getDashboardAnalytics(): array
    {
        return [
            'overview' => $this->getOverviewStats(),
            'performance' => $this->getPerformanceMetrics(),
            'trading_habits' => $this->getTradingHabits(),
            'monthly_progress' => $this->getMonthlyProgress(),
            'challenge_status' => $this->getChallengeStatus(),
            'recommendations' => $this->getRecommendations(),
        ];
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats(): array
    {
        $totalTrades = JournalEntry::where('user_id', $this->user->id)
            ->where('trade_type', '!=', 'analysis')
            ->count();

        $winningTrades = JournalEntry::where('user_id', $this->user->id)
            ->where('result', 'win')
            ->count();

        $totalProfit = JournalEntry::where('user_id', $this->user->id)
            ->sum('profit_loss');

        $avgProfitPerTrade = $totalTrades > 0 ? $totalProfit / $totalTrades : 0;

        return [
            'total_trades' => $totalTrades,
            'winning_trades' => $winningTrades,
            'losing_trades' => $totalTrades - $winningTrades,
            'total_profit' => (float) $totalProfit,
            'avg_profit_per_trade' => (float) $avgProfitPerTrade,
            'active_challenges' => Challenge::where('user_id', $this->user->id)
                ->where('status', 'active')
                ->count(),
            'active_plan' => TradingPlan::where('user_id', $this->user->id)
                ->where('is_active', true)
                ->exists(),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $metrics = [];

        // Win Rate
        $totalTrades = JournalEntry::where('user_id', $this->user->id)
            ->where('trade_type', '!=', 'analysis')
            ->count();

        $winningTrades = JournalEntry::where('user_id', $this->user->id)
            ->where('result', 'win')
            ->count();

        $metrics['win_rate'] = $totalTrades > 0 ? round(($winningTrades / $totalTrades) * 100, 1) : 0;

        // Profit Factor
        $totalProfit = JournalEntry::where('user_id', $this->user->id)
            ->where('profit_loss', '>', 0)
            ->sum('profit_loss');

        $totalLoss = abs(JournalEntry::where('user_id', $this->user->id)
            ->where('profit_loss', '<', 0)
            ->sum('profit_loss'));

        $metrics['profit_factor'] = $totalLoss > 0 ? round($totalProfit / $totalLoss, 2) : ($totalProfit > 0 ? 999 : 0);

        // Average Risk/Reward Ratio
        $tradesWithRR = JournalEntry::where('user_id', $this->user->id)
            ->whereNotNull('analysis')
            ->get();

        $totalRR = 0;
        $countRR = 0;

        foreach ($tradesWithRR as $trade) {
            // Simple RR extraction from analysis text
            if (preg_match('/RR\s*[:=]\s*(\d+\.?\d*)/i', $trade->analysis, $matches)) {
                $totalRR += (float) $matches[1];
                $countRR++;
            }
        }

        $metrics['avg_rr_ratio'] = $countRR > 0 ? round($totalRR / $countRR, 2) : 0;

        // Consistency Score (based on profit consistency)
        $monthlyProfits = JournalEntry::where('user_id', $this->user->id)
            ->select(
                DB::raw('YEAR(entry_date) as year'),
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('SUM(profit_loss) as monthly_profit')
            )
            ->where('trade_type', '!=', 'analysis')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->pluck('monthly_profit')
            ->toArray();

        if (count($monthlyProfits) > 1) {
            $avgProfit = array_sum($monthlyProfits) / count($monthlyProfits);
            $variance = 0;
            foreach ($monthlyProfits as $profit) {
                $variance += pow($profit - $avgProfit, 2);
            }
            $variance /= count($monthlyProfits);
            $stdDev = sqrt($variance);
            
            // Consistency score: higher = more consistent
            $metrics['consistency_score'] = $avgProfit != 0 ? round((1 - ($stdDev / abs($avgProfit))) * 100, 0) : 0;
            $metrics['consistency_score'] = max(0, min(100, $metrics['consistency_score']));
        } else {
            $metrics['consistency_score'] = 0;
        }

        // Emotional Control Score
        $emotionalTrades = JournalEntry::where('user_id', $this->user->id)
            ->whereNotNull('emotion_score')
            ->get();

        if ($emotionalTrades->count() > 0) {
            $avgEmotionScore = $emotionalTrades->avg('emotion_score');
            // Convert to 0-100 scale (5 = best, 1 = worst)
            $metrics['emotional_control'] = round((($avgEmotionScore - 1) / 4) * 100, 0);
        } else {
            $metrics['emotional_control'] = 0;
        }

        return $metrics;
    }

    /**
     * Get trading habits analysis
     */
    private function getTradingHabits(): array
    {
        $habits = [];

        // Most traded currency pairs
        $habits['top_pairs'] = JournalEntry::where('user_id', $this->user->id)
            ->whereNotNull('currency_pair')
            ->select('currency_pair', DB::raw('COUNT(*) as trade_count'))
            ->groupBy('currency_pair')
            ->orderByDesc('trade_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'pair' => $item->currency_pair,
                    'count' => $item->trade_count,
                    'win_rate' => $this->calculatePairWinRate($item->currency_pair),
                ];
            });

        // Best performing pairs
        $habits['best_pairs'] = JournalEntry::where('user_id', $this->user->id)
            ->whereNotNull('currency_pair')
            ->select('currency_pair', DB::raw('SUM(profit_loss) as total_profit'))
            ->groupBy('currency_pair')
            ->orderByDesc('total_profit')
            ->limit(3)
            ->get();

        // Trading time analysis
        $habits['trading_times'] = [
            'morning' => JournalEntry::where('user_id', $this->user->id)
                ->whereTime('created_at', '>=', '06:00:00')
                ->whereTime('created_at', '<', '12:00:00')
                ->count(),
            'afternoon' => JournalEntry::where('user_id', $this->user->id)
                ->whereTime('created_at', '>=', '12:00:00')
                ->whereTime('created_at', '<', '18:00:00')
                ->count(),
            'evening' => JournalEntry::where('user_id', $this->user->id)
                ->whereTime('created_at', '>=', '18:00:00')
                ->whereTime('created_at', '<', '24:00:00')
                ->count(),
            'night' => JournalEntry::where('user_id', $this->user->id)
                ->whereTime('created_at', '>=', '00:00:00')
                ->whereTime('created_at', '<', '06:00:00')
                ->count(),
        ];

        // Market condition performance
        $habits['market_conditions'] = JournalEntry::where('user_id', $this->user->id)
            ->whereNotNull('market_condition')
            ->select('market_condition', DB::raw('AVG(profit_loss) as avg_profit'))
            ->groupBy('market_condition')
            ->get()
            ->keyBy('market_condition');

        return $habits;
    }

    /**
     * Calculate win rate for a specific currency pair
     */
    private function calculatePairWinRate(string $pair): float
    {
        $totalTrades = JournalEntry::where('user_id', $this->user->id)
            ->where('currency_pair', $pair)
            ->where('trade_type', '!=', 'analysis')
            ->count();

        $winningTrades = JournalEntry::where('user_id', $this->user->id)
            ->where('currency_pair', $pair)
            ->where('result', 'win')
            ->count();

        return $totalTrades > 0 ? round(($winningTrades / $totalTrades) * 100, 1) : 0;
    }

    /**
     * Get monthly progress data
     */
    private function getMonthlyProgress(): array
    {
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $monthData = JournalEntry::where('user_id', $this->user->id)
                ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
                ->select(
                    DB::raw('COUNT(*) as total_trades'),
                    DB::raw('SUM(CASE WHEN result = "win" THEN 1 ELSE 0 END) as winning_trades'),
                    DB::raw('SUM(profit_loss) as total_profit'),
                    DB::raw('AVG(profit_loss) as avg_profit')
                )
                ->first();

            $months[] = [
                'month' => $date->format('M Y'),
                'total_trades' => (int) ($monthData->total_trades ?? 0),
                'winning_trades' => (int) ($monthData->winning_trades ?? 0),
                'total_profit' => (float) ($monthData->total_profit ?? 0),
                'avg_profit' => (float) ($monthData->avg_profit ?? 0),
                'win_rate' => $monthData->total_trades > 0 
                    ? round(($monthData->winning_trades / $monthData->total_trades) * 100, 1)
                    : 0,
            ];
        }

        return $months;
    }

    /**
     * Get challenge status
     */
    private function getChallengeStatus(): array
    {
        $activeChallenge = Challenge::where('user_id', $this->user->id)
            ->where('status', 'active')
            ->first();

        if (!$activeChallenge) {
            return ['has_active' => false];
        }

        $daysCompleted = $activeChallenge->days()->count();
        $totalDays = 30;
        $progress = ($daysCompleted / $totalDays) * 100;

        $totalProfit = $activeChallenge->days()->sum('actual_result');
        $targetProfit = $activeChallenge->target_capital - $activeChallenge->initial_capital;
        $profitProgress = $targetProfit != 0 ? ($totalProfit / $targetProfit) * 100 : 0;

        return [
            'has_active' => true,
            'challenge' => [
                'id' => $activeChallenge->id,
                'start_date' => $activeChallenge->started_at->format('d M Y'),
                'days_completed' => $daysCompleted,
                'total_days' => $totalDays,
                'progress' => round($progress, 1),
                'current_capital' => (float) $activeChallenge->current_capital,
                'target_capital' => (float) $activeChallenge->target_capital,
                'total_profit' => (float) $totalProfit,
                'target_profit' => (float) $targetProfit,
                'profit_progress' => round($profitProgress, 1),
                'remaining_days' => $totalDays - $daysCompleted,
            ],
        ];
    }

    /**
     * Get personalized recommendations
     */
    private function getRecommendations(): array
    {
        $recommendations = [];
        $metrics = $this->getPerformanceMetrics();
        $habits = $this->getTradingHabits();

        // Win rate recommendation
        if ($metrics['win_rate'] < 40) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Improve Win Rate',
                'message' => 'Your win rate is below 40%. Consider improving your entry timing or risk management.',
                'action' => 'Review losing trades to identify patterns',
            ];
        } elseif ($metrics['win_rate'] > 60) {
            $recommendations[] = [
                'type' => 'success',
                'title' => 'Excellent Win Rate',
                'message' => 'Your win rate is above 60%! Consider increasing position size slightly.',
                'action' => 'Gradually increase risk per trade by 0.5%',
            ];
        }

        // Profit factor recommendation
        if ($metrics['profit_factor'] < 1.5) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Low Profit Factor',
                'message' => 'Your profit factor is below 1.5. Focus on improving risk/reward ratio.',
                'action' => 'Aim for minimum 1:2 risk/reward ratio',
            ];
        }

        // Emotional control recommendation
        if ($metrics['emotional_control'] < 50) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Emotional Trading Detected',
                'message' => 'Your emotional control score is low. Emotions may be affecting your trading decisions.',
                'action' => 'Take breaks between trades and stick to your plan',
            ];
        }

        // Trading frequency recommendation
        $totalTrades = $this->getOverviewStats()['total_trades'];
        if ($totalTrades > 50) {
            $avgTradesPerMonth = $totalTrades / max(1, count($this->getMonthlyProgress()));
            if ($avgTradesPerMonth > 20) {
                $recommendations[] = [
                    'type' => 'info',
                    'title' => 'High Trading Frequency',
                    'message' => 'You\'re taking many trades. Quality over quantity often yields better results.',
                    'action' => 'Focus on higher probability setups only',
                ];
            }
        }

        // Add generic recommendations if less than 3
        if (count($recommendations) < 3) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Keep a Trading Journal',
                'message' => 'Consistently logging your trades helps identify patterns and improve over time.',
                'action' => 'Log every trade with detailed analysis',
            ];

            $recommendations[] = [
                'type' => 'info',
                'title' => 'Review Your Plan Weekly',
                'message' => 'Regular review of your trading plan ensures you stay on track with your goals.',
                'action' => 'Schedule weekly review sessions',
            ];
        }

        return array_slice($recommendations, 0, 5); // Max 5 recommendations
    }

    /**
     * Get quick stats for dashboard header
     */
    public function getQuickStats(): array
    {
        $overview = $this->getOverviewStats();
        $metrics = $this->getPerformanceMetrics();

        return [
            'win_rate' => $metrics['win_rate'],
            'total_profit' => $overview['total_profit'],
            'total_trades' => $overview['total_trades'],
            'profit_factor' => $metrics['profit_factor'],
            'active_challenges' => $overview['active_challenges'],
        ];
    }
}