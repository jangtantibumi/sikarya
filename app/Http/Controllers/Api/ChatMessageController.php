<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatChannelService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ChatMessageController extends Controller
{
    public function __construct(
        private readonly WorkflowNotificationService $notifications,
        private readonly ChatChannelService $channels,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'channel' => ['nullable', Rule::in(array_keys(ChatChannelService::CHANNEL_ROLES))],
            'after_id' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,200'],
        ]);

        $channels = $this->allowedChannels($request->user());
        $requestedChannel = $validated['channel'] ?? null;
        if ($requestedChannel && ! in_array($requestedChannel, $channels, true)) {
            abort(403, 'Anda tidak memiliki akses ke kanal ini.');
        }

        $query = ChatMessage::query()
            ->with('sender:id,name,username,role,job_title')
            ->whereIn('channel', $requestedChannel ? [$requestedChannel] : $channels)
            ->when(isset($validated['after_id']), fn ($builder) => $builder->where('id', '>', $validated['after_id']));

        $messages = isset($validated['after_id'])
            ? $query->orderBy('id')->limit($validated['limit'] ?? 200)->get()
            : $query->latest('id')->limit($validated['limit'] ?? 100)->get()->reverse()->values();

        return response()->json([
            'channels' => $channels,
            'messages' => $messages->map(fn (ChatMessage $message) => $this->format($message)),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel' => ['required', Rule::in(array_keys(ChatChannelService::CHANNEL_ROLES))],
            'message' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp',
                'required_without:message',
            ],
            'type' => ['nullable', Rule::in(['message', 'holiday_announcement'])],
            'holiday_title' => ['nullable', 'string', 'max:180'],
            'holiday_start_date' => ['nullable', 'date'],
            'holiday_end_date' => ['nullable', 'date', 'after_or_equal:holiday_start_date'],
        ]);

        $user = $request->user();
        if (! in_array($validated['channel'], $this->allowedChannels($user), true)) {
            abort(403, 'Anda tidak memiliki akses untuk mengirim ke kanal ini.');
        }

        $type = $validated['type'] ?? 'message';
        if ($type === 'holiday_announcement' && ! $user->isHRD() && ! $user->isCEO()) {
            abort(403, 'Pengumuman hari libur hanya dapat dibuat oleh HRD atau CEO.');
        }

        if ($type === 'holiday_announcement') {
            $request->validate([
                'holiday_title' => ['required', 'string', 'max:180'],
                'holiday_start_date' => ['required', 'date'],
                'holiday_end_date' => ['required', 'date', 'after_or_equal:holiday_start_date'],
                'message' => ['required', 'string', 'max:5000'],
            ]);
            $validated['channel'] = 'general';
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat-attachments');
        }

        $message = ChatMessage::query()->create([
            'sender_id' => $user->id,
            'channel' => $validated['channel'],
            'type' => $type,
            'message' => $validated['message'] ?? null,
            'attachment_name' => $request->file('attachment')?->getClientOriginalName(),
            'attachment_path' => $attachmentPath,
            'attachment_mime' => $request->file('attachment')?->getMimeType(),
            'attachment_size' => $request->file('attachment')?->getSize(),
            'metadata' => $type === 'holiday_announcement' ? [
                'title' => $validated['holiday_title'],
                'start_date' => $validated['holiday_start_date'],
                'end_date' => $validated['holiday_end_date'],
            ] : null,
        ])->load('sender:id,name,username,role,job_title');

        if ($type === 'holiday_announcement') {
            $this->notifications->send(
                User::query()
                    ->where('is_active', true)
                    ->where('account_status', 'active')
                    ->where('id', '!=', $user->id)
                    ->get(),
                'Pengumuman Hari Libur',
                "{$validated['holiday_title']} ({$validated['holiday_start_date']} s.d. {$validated['holiday_end_date']}).",
                "chat:holiday:{$message->id}",
                'announcement',
                '/#chat',
                ['chat_message_id' => $message->id],
            );
        }

        return response()->json([
            'success' => true,
            'message' => $this->format($message),
            'server_time' => now()->toIso8601String(),
        ], 201);
    }

    public function download(Request $request, ChatMessage $chatMessage)
    {
        if (! in_array($chatMessage->channel, $this->allowedChannels($request->user()), true)) {
            abort(403, 'Anda tidak memiliki akses ke lampiran ini.');
        }

        if (! $chatMessage->attachment_path || ! Storage::exists($chatMessage->attachment_path)) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        return Storage::download(
            $chatMessage->attachment_path,
            $chatMessage->attachment_name ?: basename($chatMessage->attachment_path),
            ['Content-Type' => $chatMessage->attachment_mime ?: 'application/octet-stream'],
        );
    }

    private function allowedChannels(User $user): array
    {
        return $this->channels->allowed($user);
    }

    private function format(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'channel' => $message->channel,
            'type' => $message->type,
            'text' => $message->message,
            'sender' => $message->type === 'ai_response' ? 'ai-copilot' : $message->sender?->username,
            'senderName' => $message->type === 'ai_response' ? 'Suba-Arch Copilot' : ($message->sender?->name ?? 'Pengguna'),
            'senderRole' => $message->type === 'ai_response'
                ? ($message->metadata['model'] ?? 'Gemini')
                : ($message->sender?->job_title ?: $message->sender?->role),
            'timestamp' => $message->created_at?->toIso8601String(),
            'metadata' => $message->metadata,
            'attachment' => $message->attachment_path ? [
                'name' => $message->attachment_name,
                'mime' => $message->attachment_mime,
                'size' => $message->attachment_size,
                'download_url' => "/api/chat-messages/{$message->id}/attachment",
            ] : null,
        ];
    }
}
