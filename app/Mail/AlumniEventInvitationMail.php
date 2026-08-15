<?php

namespace App\Mail;

use App\Models\AlumniEventInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlumniEventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AlumniEventInvitation $invitation,
        public readonly User $alumni,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject("Undangan Alumni Suba-Arch: {$this->invitation->title}")
            ->view('emails.alumni-event-invitation');
    }
}
