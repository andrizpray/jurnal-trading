<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PerformanceMilestone extends Notification
{
    use Queueable;

    public function __construct(
        public string $milestone,
        public string $metric,
        public float $value,
        public string $achievement
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
        $metricLabels = [
            'win_rate' => 'Win Rate',
            'profit_factor' => 'Profit Factor',
            'consistency_score' => 'Consistency Score',
            'total_profit' => 'Total Profit',
            'total_trades' => 'Total Trades',
        ];

        $metricLabel = $metricLabels[$this->metric] ?? ucfirst(str_replace('_', ' ', $this->metric));
        
        return (new MailMessage)
            ->subject('🏆 Performance Milestone Achieved!')
            ->greeting('Congratulations ' . $notifiable->name . '! 🎉')
            ->line('You\'ve achieved a new performance milestone!')
            ->line('')
            ->line('**Milestone:** ' . $this->milestone)
            ->line('**Metric:** ' . $metricLabel)
            ->line('**Value:** ' . $this->value . ($this->metric === 'win_rate' || $this->metric === 'consistency_score' ? '%' : ''))
            ->line('**Achievement:** ' . $this->achievement)
            ->line('')
            ->action('View Your Dashboard', url('/dashboard'))
            ->line('Keep up the excellent work! Your consistency is paying off.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'performance_milestone',
            'milestone' => $this->milestone,
            'metric' => $this->metric,
            'value' => $this->value,
            'message' => $this->achievement,
            'action_url' => '/dashboard',
            'icon' => 'trophy',
            'color' => 'yellow',
        ];
    }
}