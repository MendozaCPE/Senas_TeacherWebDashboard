<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationOtpNotification extends Notification
{
    public string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('SEÑAS Account Verification Code: ' . $this->otp)
            ->view('emails.registration-otp', [
                'otp' => $this->otp,
                'email' => is_string($notifiable) ? $notifiable : ($notifiable->email ?? ''),
            ]);
    }
}
