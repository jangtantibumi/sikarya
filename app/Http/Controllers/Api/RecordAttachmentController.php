<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\JournalEntry;
use App\Models\KpiPlan;
use App\Models\ProjectCost;
use App\Models\RecordAttachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecordAttachmentController extends Controller
{
    public function download(Request $request, RecordAttachment $recordAttachment)
    {
        $record = $recordAttachment->attachable;
        $viewer = $request->user();

        $authorized = match (true) {
            $record instanceof JournalEntry => $viewer->isCEO() || $viewer->divisionKey() === 'finance',
            $record instanceof ProjectCost => $viewer->isCEO()
                || in_array($viewer->divisionKey(), ['operasional', 'finance'], true),
            $record instanceof Task => $viewer->isCEO()
                || $record->user_id === $viewer->id
                || $record->created_by_id === $viewer->id
                || ($viewer->isManager() && $record->user && $viewer->isManagerOf($record->user)),
            $record instanceof KpiPlan => $viewer->isCEO()
                || (
                    $record->divisionKey() === $viewer->divisionKey()
                    && ($record->status === 'approved' || $record->manager_id === $viewer->id)
                ),
            $record instanceof Goal => $viewer->isCEO()
                || $record->division === $viewer->divisionKey(),
            default => false,
        };

        abort_unless($authorized, 403);
        abort_unless(
            $recordAttachment->stored_path
                && Storage::disk('local')->exists($recordAttachment->stored_path),
            404,
        );

        return Storage::disk('local')->download(
            $recordAttachment->stored_path,
            $recordAttachment->original_name,
            ['Content-Type' => $recordAttachment->mime_type ?: 'application/octet-stream'],
        );
    }
}
