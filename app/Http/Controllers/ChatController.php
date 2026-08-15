<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getMessages(Request $request, $channel)
    {
        $user = Auth::user();

        if ($channel === 'managers') {
            if (!$user->isCEO() && !$user->isManager()) {
                return response()->json(['error' => 'Unauthorized access to managers channel'], 403);
            }
        } elseif ($channel !== 'general' && !$user->isCEO() && !$user->isManager()) {
            // Employee accessing a division channel
            $userDiv = $user->divisionLabel();
            // If channel string does not contain their division name (case insensitive)
            if (stripos($channel, $userDiv) === false) {
                return response()->json(['error' => 'Unauthorized access to this channel'], 403);
            }
        }

        $query = ChatMessage::with(['sender' => function($q) {
                $q->select('id', 'name', 'role', 'company_id');
            }])
            ->where('channel', $channel)
            ->where('company_id', Auth::user()->company_id ?? 1);

        if ($request->has('after_id')) {
            $messages = $query->where('id', '>', $request->after_id)
                              ->orderBy('id', 'asc')
                              ->limit(100)
                              ->get();
        } else {
            $messages = $query->orderBy('id', 'desc')
                              ->limit(50)
                              ->get()
                              ->reverse()
                              ->values();
        }
            
        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240'
        ]);

        if (empty($request->message) && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment is required'], 422);
        }

        $user = Auth::user();
        if ($request->channel === 'managers') {
            if (!$user->isCEO() && !$user->isManager()) {
                return response()->json(['error' => 'Unauthorized to send message to managers channel'], 403);
            }
        } elseif ($request->channel !== 'general' && !$user->isCEO() && !$user->isManager()) {
            // Employee sending to a division channel
            $userDiv = $user->divisionLabel();
            if (stripos($request->channel, $userDiv) === false) {
                return response()->json(['error' => 'Unauthorized to send to this channel'], 403);
            }
        }
        
        $attachmentData = ['type' => 'text'];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat_attachments', 'public');
            $attachmentData = [
                'type' => 'file',
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_path' => '/storage/' . $path,
                'attachment_mime' => $file->getMimeType(),
                'attachment_size' => $file->getSize(),
            ];
        }

        $message = ChatMessage::create(array_merge([
            'sender_id' => Auth::id(),
            'channel' => $request->channel,
            'message' => $request->message ?? '',
            'company_id' => Auth::user()->company_id ?? 1,
        ], $attachmentData));

        return response()->json($message->load('sender'));
    }

    public function getChannels(Request $request)
    {
        $companyId = Auth::user()->company_id ?? 1;
        $user = Auth::user();
        
        $query = \App\Models\CompanyDivision::where('company_id', $companyId);
        $customQuery = \App\Models\ChatChannel::where('company_id', $companyId);
        
        if (!$user->isCEO() && !$user->isManager()) {
            $userDiv = $user->divisionLabel();
            $query->where('name', 'LIKE', '%' . $userDiv . '%');
            // For now, allow employees to see all group channels for their company or add logic if needed
            // e.g. customQuery->where('type', 'group');
        }
        
        $divisions = $query->get();
        $customChannels = $customQuery->get();
        
        return response()->json([
            'divisions' => $divisions,
            'custom' => $customChannels
        ]);
    }

    public function createChannel(Request $request)
    {
        $user = Auth::user();
        if (!$user->isCEO()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $channel = \App\Models\ChatChannel::create([
            'company_id' => $user->company_id ?? 1,
            'name' => $request->name,
            'description' => $request->description,
            'type' => 'group'
        ]);

        return response()->json(['success' => true, 'channel' => $channel]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->isCEO()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $message = ChatMessage::findOrFail($id);
        $message->delete();
        
        return response()->json(['success' => true]);
    }
}
