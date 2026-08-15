<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('target_type')->default('all')->after('content')->comment('all, role, user');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type')->comment('role_id or user_id depending on target_type');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'target_id']);
        });
    }
};
