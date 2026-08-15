<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\ChatChannelService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class GeminiController extends Controller
{
    public function __construct(
        private readonly GeminiService $gemini,
        private readonly ChatChannelService $channels,
    ) {
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $configured = $this->gemini->configured($user);

        return response()->json([
            'configured' => $configured,
            'model' => $this->gemini->model($user),
            'configured_at' => $user->gemini_configured_at?->toIso8601String(),
            'available_models' => [
                ['id' => 'gemini-2.5-flash', 'label' => 'Gemini 2.5 Flash — seimbang'],
                ['id' => 'gemini-2.5-flash-lite', 'label' => 'Gemini 2.5 Flash-Lite — lebih hemat'],
            ],
            'message' => $configured
                ? 'Gemini pribadi akun ini terhubung dan siap digunakan.'
                : 'Akun ini belum memiliki API key Gemini pribadi.',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'api_key' => ['required', 'string', 'min:20', 'max:500'],
            'model' => ['required', Rule::in(GeminiService::MODELS)],
        ]);

        try {
            $this->gemini->verifyCredential($validated['api_key'], $validated['model']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'GEMINI_CREDENTIAL_REJECTED',
            ], 422);
        }

        $user = $request->user();
        $user->forceFill([
            'gemini_api_key' => $validated['api_key'],
            'gemini_model' => $validated['model'],
            'gemini_configured_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'configured' => true,
            'model' => $validated['model'],
            'configured_at' => $user->gemini_configured_at?->toIso8601String(),
            'message' => 'API key Gemini pribadi berhasil diuji dan disimpan secara terenkripsi.',
        ]);
    }

    public function destroySettings(Request $request)
    {
        $request->user()->forceFill([
            'gemini_api_key' => null,
            'gemini_model' => null,
            'gemini_configured_at' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'configured' => false,
            'message' => 'Koneksi Gemini pribadi telah dihapus dari akun ini.',
        ]);
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:3000'],
            'channel' => ['nullable', Rule::in(array_keys(ChatChannelService::CHANNEL_ROLES))],
            'persist_to_chat' => ['nullable', 'boolean'],
            'conversation' => ['nullable', 'array', 'max:8'],
            'conversation.*.role' => ['required_with:conversation', Rule::in(['user', 'assistant'])],
            'conversation.*.text' => ['required_with:conversation', 'string', 'max:3000'],
        ]);

        $user = $request->user();
        $channel = $validated['channel'] ?? null;
        if ($channel && !$this->channels->canAccess($user, $channel)) {
            abort(403, 'Anda tidak memiliki akses ke kanal tersebut.');
        }

        if (!$this->gemini->configured($user)) {
            return response()->json([
                'message' => 'Gemini pribadi belum aktif. Buka pengaturan Copilot dan masukkan API key akun Anda.',
                'code' => 'GEMINI_NOT_CONFIGURED',
            ], 503);
        }

        try {
            $answer = $this->gemini->ask(
                $user,
                $validated['question'],
                $channel,
                $validated['conversation'] ?? [],
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'GEMINI_REQUEST_FAILED',
            ], 502);
        }

        $chatMessage = null;
        if ($channel && ($validated['persist_to_chat'] ?? false)) {
            $chatMessage = ChatMessage::query()->create([
                'sender_id' => $user->id,
                'channel' => $channel,
                'type' => 'ai_response',
                'message' => $answer,
                'metadata' => [
                    'model' => $this->gemini->model($user),
                    'requested_by' => $user->username,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'answer' => $answer,
            'model' => $this->gemini->model($user),
            'chat_message' => $chatMessage ? [
                'id' => $chatMessage->id,
                'channel' => $chatMessage->channel,
                'type' => 'ai_response',
                'text' => $chatMessage->message,
                'sender' => 'ai-copilot',
                'senderName' => 'Suba-Arch Copilot',
                'senderRole' => $this->gemini->model($user),
                'timestamp' => $chatMessage->created_at?->toIso8601String(),
                'metadata' => $chatMessage->metadata,
                'attachment' => null,
            ] : null,
        ]);
    }
}
