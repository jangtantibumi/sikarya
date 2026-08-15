<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CeoLoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build()
    {
        return $this->subject("Notifikasi Login Akun: {$this->user->name}")
            ->html("<p>Notifikasi Keamanan ERP: Akun <strong>{$this->user->name}</strong> ({$this->user->username}) berhasil login pada ".now()->format('Y-m-d H:i:s').'.</p>');
    }
}
