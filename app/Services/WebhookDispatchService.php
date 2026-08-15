<?php

namespace App\Services;

use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDispatchService
{
    /**
     * Dispatch an event to all active subscribers for the current company.
     * Note: In a real production environment, this should be queued.
     * For localhost/demo purposes, we execute synchronously but catch errors.
     */
    public function dispatch(int $companyId, string $eventName, array $payload): void
    {
        $subscriptions = WebhookSubscription::query()
            ->where('company_id', $companyId)
            ->where('event_name', $eventName)
            ->where('is_active', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $headers = [];
                if ($subscription->secret) {
                    $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($payload), $subscription->secret);
                }

                Http::withHeaders($headers)
                    ->timeout(5)
                    ->post($subscription->url, [
                        'event' => $eventName,
                        'timestamp' => now()->toIso8601String(),
                        'data' => $payload,
                    ]);
                    
                Log::info("Webhook dispatched for event {$eventName} to {$subscription->url}");
            } catch (\Exception $e) {
                Log::error("Failed to dispatch webhook to {$subscription->url}: " . $e->getMessage());
            }
        }
    }
}
