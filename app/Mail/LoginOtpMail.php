<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly int $expiresMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Login ERP Suba-Arch',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
