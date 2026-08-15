<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected ?string $idempotencyKey = null,
        protected string $title = 'Pembaruan Sistem',
        protected string $category = 'workflow',
        protected ?string $actionUrl = null,
        protected array $meta = [],
    ) {
        $this->idempotencyKey ??= uniqid('notify_', true);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'key' => $this->idempotencyKey,
            'category' => $this->category,
            'action_url' => $this->actionUrl,
            'meta' => $this->meta,
        ];
    }
}
