<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users');
            $table->string('channel', 60)->index();
            $table->string('type', 40)->default('message');
            $table->text('message')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_mime', 150)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('resignation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('last_working_date');
            $table->text('reason');
            $table->text('handover_notes')->nullable();
            $table->string('status')->default('pending_manager');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignation_requests');
        Schema::dropIfExists('chat_messages');
    }
};
