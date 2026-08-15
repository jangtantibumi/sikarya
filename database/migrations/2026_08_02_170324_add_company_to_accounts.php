<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['code']);
            if (Schema::hasColumn('accounts', 'system_key')) {
                $table->dropUnique(['system_key']);
            }
            
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            
            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'system_key']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->dropUnique(['company_id', 'system_key']);
            $table->dropConstrainedForeignId('company_id');
            $table->unique('code');
            $table->unique('system_key');
        });
    }
};
