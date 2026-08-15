<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('gemini_api_key')->nullable();
            $table->string('gemini_model', 80)->nullable();
            $table->timestamp('gemini_configured_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['gemini_api_key', 'gemini_model', 'gemini_configured_at']);
        });
    }
};
