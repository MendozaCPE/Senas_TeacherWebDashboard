<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentActivityMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $notifTitle;
    public string $notifMessage;
    public string $notifType;
    public string $teacherName;
    public ?string $actionUrl;

    public function __construct(
        string  $teacherName,
        string  $notifTitle,
        string  $notifMessage,
        string  $notifType  = 'general',
        ?string $actionUrl  = null,
    ) {
        $this->teacherName   = $teacherName;
        $this->notifTitle    = $notifTitle;
        $this->notifMessage  = $notifMessage;
        $this->notifType     = $notifType;
        $this->actionUrl     = $actionUrl ? url($actionUrl) : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SEÑAS] ' . $this->notifTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-activity',
        );
    }
}
