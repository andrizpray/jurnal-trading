<?php

namespace App\Notifications;

use App\Models\TradingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradingPlanUpdate extends Notification
{
    use Queueable;

    public function __construct(
        public TradingPlan $plan
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        
        // Only send mail if SMTP credentials are configured
        if (!empty(config('mail.mailers.smtp.username'))) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $growth = $this->plan->growth_pct ?? 0;
        
        return (new MailMessage)
            ->subject('📈 Trading Plan Update Available')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your trading plan has been updated with new calculations.')
            ->line('')
            ->line('**Plan Details:**')
            ->line('• **Capital:** ' . number_format($this->plan->capital, 0))
            ->line('• **Trader Type:** ' . ucfirst($this->plan->trader_type))
            ->line('• **Currency Pair:** ' . $this->plan->currency_pair)
            ->line('• **Risk/Reward:** 1:' . $this->plan->rr_ratio)
            ->line('• **Projected Growth:** ' . $growth . '% over 30 days')
            ->line('')
            ->action('View Updated Plan', url('/trading-plan'))
            ->line('Review your plan and make adjustments if needed.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'trading_plan_update',
            'plan_id' => $this->plan->id,
            'message' => 'Your trading plan has been updated',
            'action_url' => '/trading-plan',
            'icon' => 'chart-line',
            'color' => 'green',
        ];
    }
}