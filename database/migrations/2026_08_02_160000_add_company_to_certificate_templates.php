<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('certificate_templates') && !Schema::hasColumn('certificate_templates', 'company_id')) {
            Schema::table('certificate_templates', function (Blueprint $t) {
                $t->foreignId('company_id')->nullable();
                $t->foreign('company_id', 'certificate_templates_company_id_fk')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('certificate_templates') && Schema::hasColumn('certificate_templates', 'company_id')) {
            Schema::table('certificate_templates', function (Blueprint $t) {
                $t->dropForeign('certificate_templates_company_id_fk');
                $t->dropColumn('company_id');
            });
        }
    }
};
