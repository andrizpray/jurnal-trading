<?php

namespace App\Services;

use App\Models\User;
use App\Models\Challenge;
use App\Models\TradingPlan;
use App\Notifications\DailyChallengeReminder;
use App\Notifications\TradingPlanUpdate;
use App\Notifications\PerformanceMilestone;
use App\Services\AnalyticsService;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send daily challenge reminders to users with active challenges
     */
    public function sendChallengeReminders(): void
    {
        $activeChallenges = Challenge::where('status', 'active')
            ->with('user')
            ->get();

        foreach ($activeChallenges as $challenge) {
            $dayNumber = $challenge->current_day + 1;
            
            // Don't send if challenge is completed
            if ($dayNumber > 30) {
                continue;
            }

            // Check if user has already updated for today
            $todayUpdate = $challenge->days()
                ->whereDate('completed_at', Carbon::today())
                ->exists();

            if (!$todayUpdate) {
                $challenge->user->notify(new DailyChallengeReminder($challenge, $dayNumber));
            }
        }
    }

    /**
     * Send trading plan update notifications
     */
    public function sendPlanUpdateNotifications(TradingPlan $plan): void
    {
        try {
            $plan->user->notify(new TradingPlanUpdate($plan));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send plan update notification: ' . $e->getMessage());
        }
    }

    /**
     * Check and send performance milestone notifications
     */
    public function checkPerformanceMilestones(User $user): void
    {
        $analyticsService = new AnalyticsService($user);
        $metrics = $analyticsService->getPerformanceMetrics();
        $overview = $analyticsService->getOverviewStats();

        $allMetrics = array_merge($metrics, [
            'total_profit' => $overview['total_profit'],
            'total_trades' => $overview['total_trades'],
        ]);

        $milestones = $this->getMilestoneConfig();

        foreach ($milestones as $metric => $levels) {
            if (!isset($allMetrics[$metric])) {
                continue;
            }

            $currentValue = $allMetrics[$metric];
            
            foreach ($levels as $threshold => $achievement) {
                if ($currentValue >= $threshold) {
                    // Check if user already received this milestone
                    $alreadyReceived = $user->notifications()
                        ->where('type', PerformanceMilestone::class)
                        ->whereJsonContains('data->metric', $metric)
                        ->whereJsonContains('data->milestone', $achievement['title'])
                        ->exists();

                    if (!$alreadyReceived) {
                        $user->notify(new PerformanceMilestone(
                            $achievement['title'],
                            $metric,
                            $currentValue,
                            $achievement['message']
                        ));
                        break; // Only send highest achieved milestone
                    }
                }
            }
        }
    }

    /**
     * Get milestone configuration
     */
    private function getMilestoneConfig(): array
    {
        return [
            'win_rate' => [
                50 => [
                    'title' => 'Consistent Trader',
                    'message' => 'Achieved 50%+ win rate! You\'re consistently profitable.',
                ],
                60 => [
                    'title' => 'Expert Trader',
                    'message' => 'Achieved 60%+ win rate! Your trading skills are exceptional.',
                ],
                70 => [
                    'title' => 'Master Trader',
                    'message' => 'Achieved 70%+ win rate! You\'re among the top traders.',
                ],
            ],
            'profit_factor' => [
                1.5 => [
                    'title' => 'Risk Manager',
                    'message' => 'Achieved 1.5+ profit factor! Excellent risk management.',
                ],
                2.0 => [
                    'title' => 'Profit Master',
                    'message' => 'Achieved 2.0+ profit factor! Outstanding profitability.',
                ],
                3.0 => [
                    'title' => 'Trading Legend',
                    'message' => 'Achieved 3.0+ profit factor! Legendary performance.',
                ],
            ],
            'consistency_score' => [
                70 => [
                    'title' => 'Steady Performer',
                    'message' => 'Achieved 70%+ consistency score! Very stable performance.',
                ],
                85 => [
                    'title' => 'Rock Solid',
                    'message' => 'Achieved 85%+ consistency score! Incredibly consistent.',
                ],
                95 => [
                    'title' => 'Machine-like Consistency',
                    'message' => 'Achieved 95%+ consistency score! Unbelievably consistent.',
                ],
            ],
            'total_trades' => [
                50 => [
                    'title' => 'Experienced Trader',
                    'message' => 'Completed 50+ trades! Gaining valuable experience.',
                ],
                100 => [
                    'title' => 'Veteran Trader',
                    'message' => 'Completed 100+ trades! Significant trading experience.',
                ],
                250 => [
                    'title' => 'Seasoned Professional',
                    'message' => 'Completed 250+ trades! Extensive trading experience.',
                ],
            ],
            'total_profit' => [
                1000000 => [
                    'title' => 'Millionaire Maker',
                    'message' => 'Generated 1,000,000+ profit! Major milestone achieved.',
                ],
                5000000 => [
                    'title' => 'Trading Champion',
                    'message' => 'Generated 5,000,000+ profit! Exceptional results.',
                ],
                10000000 => [
                    'title' => 'Trading Elite',
                    'message' => 'Generated 10,000,000+ profit! Elite level performance.',
                ],
            ],
        ];
    }

    /**
     * Get unread notifications count for user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentNotifications(User $user, int $limit = 10): array
    {
        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'icon' => $notification->data['icon'] ?? 'bell',
                    'color' => $notification->data['color'] ?? 'gray',
                    'message' => $notification->data['message'] ?? '',
                    'action_url' => $notification->data['action_url'] ?? null,
                ];
            })
            ->toArray();
    }
}