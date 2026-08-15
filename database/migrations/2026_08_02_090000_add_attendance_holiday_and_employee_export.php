<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->boolean('is_holiday_work')->default(false)->after('work_type');
        });

        Schema::table('employee_separations', function (Blueprint $table): void {
            $table->string('backup_path')->nullable()->after('converted_to_alumni');
        });
    }

    public function down(): void
    {
        Schema::table('employee_separations', function (Blueprint $table): void {
            $table->dropColumn('backup_path');
        });
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn('is_holiday_work');
        });
    }
};
