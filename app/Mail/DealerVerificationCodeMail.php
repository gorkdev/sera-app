<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DealerVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly int $expiresMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sera - E-posta doğrulama kodunuz',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dealer-verification-code',
            with: [
                'code' => $this->code,
                'expiresMinutes' => $this->expiresMinutes,
            ],
        );
    }
}

