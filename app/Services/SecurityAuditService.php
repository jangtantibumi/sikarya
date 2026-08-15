<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SecurityAuditService
{
    public function logRbac(
        User $actor,
        User $target,
        string $action,
        array $beforeState = [],
        array $afterState = [],
        ?string $ipAddress = null
    ): void {
        AuditLog::create([
            'user_id' => $actor->id,
            'target_user_id' => $target->id,
            'action' => $action,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'ip_address' => $ipAddress,
        ]);
    }

    public function record(
        string $eventType,
        ?User $actor = null,
        ?Request $request = null,
        array $metadata = [],
        ?string $subjectType = null,
        string|int|null $subjectId = null,
    ): ?AuditEvent {
        if (! Schema::hasTable('audit_events')) {
            return null;
        }

        $previousHash = AuditEvent::query()->latest('id')->value('event_hash');
        $createdAt = now();
        $safeMetadata = collect($metadata)
            ->except(['password', 'otp', 'api_key', 'gemini_api_key'])
            ->put('_event_nonce', bin2hex(random_bytes(8)))
            ->all();
        $payload = [
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId === null ? null : (string) $subjectId,
            'ip_address' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 1000),
            'metadata' => $safeMetadata,
            'previous_hash' => $previousHash,
            'created_at' => $createdAt->format('Y-m-d\TH:i:s.uP'),
        ];
        $key = (string) config('app.key', 'erp-audit-key');
        $eventHash = hash_hmac(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $key,
        );

        return AuditEvent::query()->create([
            ...$payload,
            'event_hash' => $eventHash,
            'created_at' => $createdAt,
        ]);
    }
}
