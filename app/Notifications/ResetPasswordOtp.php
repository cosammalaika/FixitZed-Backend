<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordOtp extends Notification
{
    public function __construct(
        protected string $otp,
        protected ?string $appName = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = $this->appName ?: config('app.name', 'FixitZed');

        return (new MailMessage())
            ->subject("{$app} password reset code")
            ->greeting('Hello!')
            ->line("We received a request to reset the password for your {$app} account.")
            ->line('Use the one-time code below to complete the reset in the app:')
            ->line("**{$this->otp}**")
            ->line('This code expires in 15 minutes. If you did not request the reset, you can ignore this email.')
            ->salutation('— The FixitZed Team');
    }
}

