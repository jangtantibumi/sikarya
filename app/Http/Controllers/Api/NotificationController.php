<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(max((int) $request->integer('limit', 50), 1), 100);
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Pembaruan Sistem',
                'message' => $notification->data['message'] ?? '',
                'category' => $notification->data['category'] ?? 'workflow',
                'action_url' => $notification->data['action_url'] ?? null,
                'meta' => $notification->data['meta'] ?? [],
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toIso8601String(),
            ])->values(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function markRead(Request $request, string $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
