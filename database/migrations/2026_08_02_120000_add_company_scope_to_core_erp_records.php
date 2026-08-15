<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'attendances', 'goals', 'kpi_plans', 'kpis', 'approval_requests',
        'leave_requests', 'resignation_requests', 'chat_messages', 'erp_documents',
        'projects', 'project_costs', 'talent_reviews', 'metric_snapshots',
        'journal_entries', 'client_inflows', 'record_attachments',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->foreignId('company_id')->nullable();
                $table->foreign('company_id', substr($tableName, 0, 40) . '_company_id_fk')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropForeign(substr($tableName, 0, 40) . '_company_id_fk');
                $table->dropColumn('company_id');
            });
        }
    }
};
