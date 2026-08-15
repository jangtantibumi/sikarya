<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\ClientInflow;
use App\Models\DataDeletionRequest;
use App\Models\ErpDocument;
use App\Models\Goal;
use App\Models\JournalEntry;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\Lead;
use App\Models\ProjectCost;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DataDeletionExecutor
{
    public function __construct(
        private readonly AccountingService $accounting,
        private readonly MetricAggregationService $metrics,
        private readonly SecurityAuditService $audit,
    ) {}

    public function execute(DataDeletionRequest $deletion, User $actor): DataDeletionRequest
    {
        if ($deletion->status === 'executed') {
            return $deletion;
        }

        return DB::transaction(function () use ($deletion, $actor): DataDeletionRequest {
            /** @var DataDeletionRequest $locked */
            $locked = DataDeletionRequest::query()->lockForUpdate()->findOrFail($deletion->id);
            if ($locked->status === 'executed') {
                return $locked;
            }

            $target = $this->findTarget($locked);

            match ($locked->deletion_mode) {
                'redact' => $this->redactChat($target, $locked),
                'revoke' => $this->revokeDocument($target, $locked),
                'reverse' => $this->reverseJournal($target, $actor, $locked),
                'reverse_and_delete' => $this->reverseAndDelete($target, $actor, $locked),
                'soft_delete' => $this->softDelete($target),
                default => throw new RuntimeException('Mode penghapusan tidak dikenali.'),
            };

            $this->recalculateMetrics($locked, $target);

            $locked->forceFill([
                'status' => 'executed',
                'executed_by_id' => $actor->id,
                'executed_at' => now(),
            ])->save();

            $this->audit->record(
                'data.deletion_executed',
                actor: $actor,
                metadata: [
                    'resource_type' => $locked->resource_type,
                    'target_label' => $locked->target_label,
                    'mode' => $locked->deletion_mode,
                    'reason' => $locked->reason,
                    'requested_by_id' => $locked->requested_by_id,
                ],
                subjectType: DataDeletionRequest::class,
                subjectId: $locked->id,
            );

            return $locked->fresh(['requester', 'executor']);
        });
    }

    private function findTarget(DataDeletionRequest $deletion): Model
    {
        $class = $deletion->target_type;
        abort_unless(is_subclass_of($class, Model::class), 422, 'Target penghapusan tidak valid.');

        $query = $class::query();
        if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            $query->withTrashed();
        }

        return $query->findOrFail($deletion->target_id);
    }

    private function softDelete(Model $target): void
    {
        if ($target instanceof Goal) {
            $target->kpiPlans()->with('kpis')->each(function (KpiPlan $plan): void {
                $plan->kpis()->delete();
                $plan->delete();
            });
        } elseif ($target instanceof KpiPlan) {
            $target->kpis()->delete();
        }

        if (method_exists($target, 'trashed') && $target->trashed()) {
            return;
        }

        $target->delete();
    }

    private function redactChat(Model $target, DataDeletionRequest $deletion): void
    {
        abort_unless($target instanceof ChatMessage, 422, 'Target redaksi pesan tidak valid.');

        if ($target->attachment_path) {
            Storage::disk('local')->delete($target->attachment_path);
        }

        $target->forceFill([
            'message' => 'Pesan telah dihapus melalui proses persetujuan.',
            'attachment_name' => null,
            'attachment_path' => null,
            'attachment_mime' => null,
            'attachment_size' => null,
            'metadata' => [
                ...($target->metadata ?? []),
                'redacted' => true,
                'redacted_at' => now()->toIso8601String(),
                'deletion_request_id' => $deletion->id,
            ],
        ])->save();
    }

    private function revokeDocument(Model $target, DataDeletionRequest $deletion): void
    {
        abort_unless($target instanceof ErpDocument, 422, 'Target pencabutan dokumen tidak valid.');

        $target->forceFill([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revocation_reason' => $deletion->reason,
        ])->save();
    }

    private function reverseJournal(Model $target, User $actor, DataDeletionRequest $deletion): void
    {
        abort_unless($target instanceof JournalEntry, 422, 'Target pembalikan jurnal tidak valid.');
        $this->accounting->reverseEntry($target, $actor, $deletion->reason);
    }

    private function reverseAndDelete(Model $target, User $actor, DataDeletionRequest $deletion): void
    {
        if ($target instanceof ClientInflow) {
            $entry = JournalEntry::query()
                ->where('source_type', 'client_inflow')
                ->where('source_id', $target->id)
                ->where('status', 'posted')
                ->first();
            if ($entry) {
                $this->accounting->reverseEntry($entry, $actor, $deletion->reason);
            }
            $this->softDelete($target);

            return;
        }

        if ($target instanceof ProjectCost) {
            if ($target->journalEntry && $target->journalEntry->status === 'posted') {
                $this->accounting->reverseEntry($target->journalEntry, $actor, $deletion->reason);
            }
            $this->softDelete($target);

            return;
        }

        throw new RuntimeException('Target pembalikan dan penghapusan tidak didukung.');
    }

    private function recalculateMetrics(DataDeletionRequest $deletion, Model $target): void
    {
        if ($target instanceof Task && $target->kpi) {
            $this->metrics->recalculateKpi($target->kpi);
        } elseif ($target instanceof Lead) {
            $this->metrics->recalculateForDataSource('leads', $deletion->division);
        } elseif ($target instanceof ClientInflow) {
            $this->metrics->recalculateForDataSource('client_inflows', 'finance');
        } elseif ($target instanceof Attendance) {
            $this->metrics->recalculateForDataSource('attendance', $deletion->division);
        } elseif ($target instanceof Kpi) {
            $this->metrics->recalculateKpiPlan($target->plan);
        } elseif ($target instanceof KpiPlan && $target->goal) {
            $this->metrics->recalculateGoal($target->goal);
        }
    }
}
