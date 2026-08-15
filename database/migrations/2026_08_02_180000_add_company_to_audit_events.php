<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('audit_events', 'company_id')) {
            Schema::table('audit_events', function (Blueprint $t) {
                $t->foreignId('company_id')->nullable();
                $t->foreign('company_id', 'audit_events_company_id_fk')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('audit_events', 'company_id')) {
            Schema::table('audit_events', function (Blueprint $t) {
                $t->dropForeign('audit_events_company_id_fk');
                $t->dropColumn('company_id');
            });
        }
    }
};
