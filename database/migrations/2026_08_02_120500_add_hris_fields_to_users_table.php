<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('reports_to_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('default_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->boolean('is_approved')->default(true);
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->integer('default_leave_quota')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['reports_to_id']);
            $table->dropForeign(['default_shift_id']);
            $table->dropColumn([
                'reports_to_id',
                'default_shift_id',
                'is_approved',
                'base_salary',
                'default_leave_quota',
            ]);
        });
    }
};
