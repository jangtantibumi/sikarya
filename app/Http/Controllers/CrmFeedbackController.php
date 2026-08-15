<?php

namespace App\Http\Controllers;

use App\Models\CrmFeedback;
use Illuminate\Http\Request;

class CrmFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = CrmFeedback::with('customer')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $feedbacks = $query->paginate(15);
        return view('crm.feedbacks.index', compact('feedbacks'));
    }

    public function show(CrmFeedback $feedback)
    {
        return view('crm.feedbacks.show', compact('feedback'));
    }

    public function update(Request $request, CrmFeedback $feedback)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved',
            'resolution_notes' => 'nullable|string',
        ]);

        $oldStatus = $feedback->status;
        $feedback->update($validated);

        if ($oldStatus !== $feedback->status) {
            \App\Models\CrmCustomerTimeline::create([
                'customer_id' => $feedback->customer_id,
                'action' => 'FEEDBACK_UPDATED',
                'description' => "Status keluhan/feedback berubah dari {$oldStatus} menjadi {$feedback->status}.",
                'reference_id' => $feedback->id,
            ]);
        }

        return redirect()->route('crm.feedbacks.show', $feedback->id)->with('success', 'Feedback berhasil diupdate');
    }

    public function destroy(CrmFeedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('crm.feedbacks.index')->with('success', 'Feedback berhasil dihapus');
    }
}
