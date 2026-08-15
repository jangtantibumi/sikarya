<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsAppCloudController extends Controller
{
    public function __construct(
        private readonly WhatsAppCloudService $whatsApp,
    ) {}

    public function status(Request $request): JsonResponse
    {
        $this->authorizeCrm($request);

        return response()->json([
            ...$this->whatsApp->status(),
            'callback_url' => route('webhooks.whatsapp.receive'),
        ]);
    }

    public function send(Request $request, string $id): JsonResponse
    {
        $this->authorizeCrm($request);
        $lead = $this->findLead($id);
        abort_unless($this->canManage($lead, $request->user()), 403);

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['text', 'template'])],
            'body' => ['required_if:mode,text', 'nullable', 'string', 'max:4096'],
            'template_name' => [
                'required_if:mode,template',
                'nullable',
                'string',
                'max:512',
                'regex:/^[a-z0-9_]+$/',
            ],
            'language' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z_]+$/'],
            'template_parameters' => ['nullable', 'array', 'max:10'],
            'template_parameters.*' => ['string', 'max:1024'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        $activity = $validated['mode'] === 'template'
            ? $this->whatsApp->sendTemplate(
                $lead,
                $request->user(),
                $validated['template_name'],
                $validated['language'] ?? 'id',
                $validated['template_parameters'] ?? [],
            )
            : $this->whatsApp->sendText($lead, $request->user(), $validated['body']);

        if (array_key_exists('next_follow_up_at', $validated)) {
            $lead->forceFill(['next_follow_up_at' => $validated['next_follow_up_at']])->save();
        }

        return response()->json([
            'message' => 'Pesan diterima oleh WhatsApp Cloud API.',
            'activity' => [
                'id' => $activity->id,
                'channel' => $activity->channel,
                'direction' => $activity->direction,
                'body' => $activity->body,
                'occurred_at' => $activity->occurred_at?->toIso8601String(),
                'delivery_status' => $activity->metadata['delivery_status'] ?? 'accepted',
            ],
        ], 201);
    }

    private function authorizeCrm(Request $request): void
    {
        abort_unless(
            $request->user()?->isCEO() || $request->user()?->divisionKey() === 'marketing',
            403,
        );
    }

    private function findLead(string $id): Lead
    {
        $numericId = preg_replace('/\D+/', '', $id);
        abort_if($numericId === '', 404);

        return Lead::query()->with('assignee')->findOrFail((int) $numericId);
    }

    private function canManage(Lead $lead, User $actor): bool
    {
        return $actor->isCEO()
            || $actor->id === $lead->assigned_to
            || ($lead->assignee && $actor->isManagerOf($lead->assignee));
    }
}
