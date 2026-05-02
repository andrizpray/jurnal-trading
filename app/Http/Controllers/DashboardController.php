<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Single AnalyticsService instance - reuse for both analytics and quick stats
        $analyticsService = new AnalyticsService($user);
        $analytics  = $analyticsService->getDashboardAnalytics();
        $quickStats = $analyticsService->getQuickStats();

        $plan      = $user->activeTradingPlan();
        $challenge = $user->activeChallenge();

        $recentEntries = $user->journalEntries()
            ->latest('entry_date')
            ->limit(5)
            ->get();

        // Daily PnL chart data (last 14 days)
        $chartData = $user->journalEntries()
            ->where('entry_date', '>=', now()->subDays(14))
            ->selectRaw('entry_date, SUM(profit_loss) as daily_pnl')
            ->groupBy('entry_date')
            ->orderBy('entry_date')
            ->get();

        return view('dashboard', compact(
            'user', 'plan', 'challenge', 'recentEntries', 'chartData',
            'analytics', 'quickStats'
        ));
    }
}
