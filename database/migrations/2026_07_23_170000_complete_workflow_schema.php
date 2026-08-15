<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            if (!Schema::hasColumn('goals', 'progress')) {
                $table->decimal('progress', 5, 2)->default(0);
            }
        });

        Schema::table('kpi_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('kpi_plans', 'score')) {
                $table->decimal('score', 5, 2)->default(0);
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('tasks', 'evidence')) {
                $table->text('evidence')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'feedback')) {
                $table->text('feedback')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'metric_value')) {
                $table->decimal('metric_value', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('tasks', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }
        });

        $this->backfillPendingTeamApprovals();
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'created_by_id')) {
                $table->dropConstrainedForeignId('created_by_id');
            }

            $columns = array_values(array_filter([
                'evidence',
                'feedback',
                'metric_value',
                'submitted_at',
                'approved_at',
                'verified_at',
            ], fn (string $column) => Schema::hasColumn('tasks', $column)));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('kpi_plans', function (Blueprint $table) {
            if (Schema::hasColumn('kpi_plans', 'score')) {
                $table->dropColumn('score');
            }
        });

        Schema::table('goals', function (Blueprint $table) {
            if (Schema::hasColumn('goals', 'progress')) {
                $table->dropColumn('progress');
            }
        });
    }

    private function backfillPendingTeamApprovals(): void
    {
        if (!Schema::hasTable('team_requests') || !Schema::hasTable('approval_requests')) {
            return;
        }

        $now = now();

        DB::table('team_requests')
            ->where('status', 'pending')
            ->orderBy('id')
            ->each(function (object $teamRequest) use ($now): void {
                $subjectType = \App\Models\TeamRequest::class;

                $exists = DB::table('approval_requests')
                    ->where('subject_type', $subjectType)
                    ->where('subject_id', $teamRequest->id)
                    ->exists();

                if ($exists) {
                    return;
                }

                $requester = DB::table('users')
                    ->where('username', $teamRequest->requester_username)
                    ->first();

                if (!$requester) {
                    return;
                }

                $isStaff = str_starts_with((string) $requester->role, 'staff_');
                $manager = $isStaff && $requester->parent
                    ? DB::table('users')->where('username', $requester->parent)->first()
                    : null;

                DB::table('approval_requests')->insert([
                    'request_type' => 'team_request',
                    'division' => $this->divisionFromRole((string) $requester->role),
                    'requester_id' => $requester->id,
                    'subject_type' => $subjectType,
                    'subject_id' => $teamRequest->id,
                    'current_approver_id' => $manager?->id,
                    'current_step' => 1,
                    'status' => $manager ? 'pending_manager' : 'pending_ceo',
                    'payload' => json_encode([
                        'action' => $teamRequest->action,
                        'target_username' => $teamRequest->target_username,
                    ], JSON_THROW_ON_ERROR),
                    'submitted_at' => $teamRequest->created_at ?? $now,
                    'completed_at' => null,
                    'created_at' => $teamRequest->created_at ?? $now,
                    'updated_at' => $now,
                ]);
            });
    }

    private function divisionFromRole(string $role): ?string
    {
        return match (true) {
            str_contains($role, 'marketing') => 'marketing',
            str_contains($role, 'ops') => 'operasional',
            str_contains($role, 'finance') => 'finance',
            str_contains($role, 'hrd'), str_contains($role, 'hr') => 'hrd',
            default => null,
        };
    }
};
