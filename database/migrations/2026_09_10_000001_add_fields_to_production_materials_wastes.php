<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_materials', function (Blueprint $table) {
            $table->decimal('issued_quantity', 15, 4)->default(0)->after('planned_quantity');
        });

        Schema::table('production_wastes', function (Blueprint $table) {
            $table->string('type')->default('waste')->after('quantity'); // 'waste', 'reject', 'scrap'
        });
    }

    public function down(): void
    {
        Schema::table('production_materials', function (Blueprint $table) {
            $table->dropColumn('issued_quantity');
        });

        Schema::table('production_wastes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
