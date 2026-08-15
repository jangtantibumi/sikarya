<?php

namespace App\Jobs;

use App\Models\WebhookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventName;
    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(string $eventName, array $payload)
    {
        $this->eventName = $eventName;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = WebhookSubscription::where('event_name', $this->eventName)
            ->where('is_active', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            $signature = hash_hmac('sha256', json_encode($this->payload), $subscription->secret_key ?? config('app.key'));

            Http::timeout(10)
                ->withHeaders([
                    'X-ERP-Signature' => $signature,
                    'X-ERP-Event' => $this->eventName,
                ])
                ->post($subscription->url, $this->payload);
        }
    }
}
