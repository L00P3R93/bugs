<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $kadiPlayPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name').' — Your Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.welcome',
            with: [
                'user' => $this->user,
                'kadiPlayPassword' => $this->kadiPlayPassword,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        );
    }
}
