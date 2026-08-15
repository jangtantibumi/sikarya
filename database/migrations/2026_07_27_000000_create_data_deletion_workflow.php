<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SOFT_DELETABLE_TABLES = [
        'tasks',
        'goals',
        'kpi_plans',
        'kpis',
        'rules',
        'leads',
        'client_inflows',
        'leave_requests',
        'resignation_requests',
        'attendances',
        'talent_reviews',
        'erp_documents',
        'projects',
        'project_costs',
    ];

    public function up(): void
    {
        Schema::create('data_deletion_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('resource_type', 80);
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->string('target_label');
            $table->string('deletion_mode', 40)->default('soft_delete');
            $table->string('scope', 40)->default('shared');
            $table->string('division', 80)->nullable();
            $table->foreignId('requested_by_id')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->string('status', 40)->default('pending_manager');
            $table->longText('snapshot')->nullable();
            $table->foreignId('executed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'target_id']);
            $table->index(['status', 'division']);
            $table->index(['requested_by_id', 'created_at']);
        });

        foreach (self::SOFT_DELETABLE_TABLES as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::SOFT_DELETABLE_TABLES) as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }

        Schema::dropIfExists('data_deletion_requests');
    }
};
