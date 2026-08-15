<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|string|in:all,managers',
        ]);

        $announcement = Announcement::create([
            'company_id' => Auth::user()->company_id ?? 1,
            'title' => $request->title,
            'content' => $request->message,
            'target_type' => $request->target,
            'target_id' => null, // Global or Role-based
            'created_by_id' => Auth::id(),
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement created successfully',
            'data' => $announcement,
        ]);
    }

    public function destroy($id)
    {
        if (! Auth::user()->isCEO()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['status' => 'success', 'message' => 'Announcement deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        if (! Auth::user()->isCEO()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $period = $request->input('period');
        $query = Announcement::query();
        $now = now();

        if ($period === 'daily') {
            $query->where('created_at', '<', $now->subDay());
        } elseif ($period === 'weekly') {
            $query->where('created_at', '<', $now->subWeek());
        } elseif ($period === 'monthly') {
            $query->where('created_at', '<', $now->subMonth());
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid period specified'], 400);
        }

        $count = $query->delete();

        return response()->json([
            'status' => 'success',
            'message' => "$count announcements deleted successfully.",
        ]);
    }
}
