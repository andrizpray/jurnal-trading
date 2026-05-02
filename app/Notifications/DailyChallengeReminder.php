<?php

namespace App\Notifications;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyChallengeReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Challenge $challenge,
        public int $dayNumber
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
        $progress = $this->challenge->progress_percent;
        $remainingDays = 30 - $this->dayNumber;
        
        return (new MailMessage)
            ->subject('📊 Daily Challenge Reminder - Day ' . $this->dayNumber)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Don\'t forget to update your trading challenge for **Day ' . $this->dayNumber . '**!')
            ->line('')
            ->line('**Challenge Progress:** ' . $progress . '%')
            ->line('**Current Capital:** ' . number_format($this->challenge->current_capital, 0))
            ->line('**Target Capital:** ' . number_format($this->challenge->target_capital, 0))
            ->line('**Remaining Days:** ' . $remainingDays)
            ->line('')
            ->action('Update Challenge', url('/challenge/' . $this->challenge->id))
            ->line('Consistency is key to success! Keep up the good work! 🚀');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'challenge_reminder',
            'challenge_id' => $this->challenge->id,
            'day_number' => $this->dayNumber,
            'message' => 'Daily challenge reminder for Day ' . $this->dayNumber,
            'action_url' => '/challenge/' . $this->challenge->id,
            'icon' => 'fire',
            'color' => 'orange',
        ];
    }
}