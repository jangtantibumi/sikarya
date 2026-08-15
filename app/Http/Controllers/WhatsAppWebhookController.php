<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppCloudService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonException;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WhatsAppCloudService $whatsApp,
    ) {
    }

    public function verify(Request $request): Response
    {
        $configuredToken = (string) config('services.whatsapp.verify_token');
        if ($configuredToken === '') {
            return response('WhatsApp webhook is not configured.', 503);
        }

        $mode = (string) ($request->query('hub.mode') ?? $request->query('hub_mode', ''));
        $token = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token', ''));
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge', ''));

        if ($mode === 'subscribe' && $challenge !== '' && hash_equals($configuredToken, $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request): Response
    {
        $payload = $request->getContent();
        if (! $this->whatsApp->verifySignature(
            $payload,
            $request->header('X-Hub-Signature-256'),
        )) {
            return response('Invalid signature', 403);
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return response('Invalid JSON', 400);
        }

        if (! is_array($decoded)) {
            return response('Invalid payload', 400);
        }

        $this->whatsApp->processWebhook($decoded);

        return response('EVENT_RECEIVED', 200)->header('Content-Type', 'text/plain');
    }
}
