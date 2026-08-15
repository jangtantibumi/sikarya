<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_attachments', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('attachable');
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 60)->default('supporting_document');
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();

            $table->index(['category', 'created_at']);
        });

        Schema::create('employee_separations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_request_id')->nullable()->constrained('team_requests')->nullOnDelete();
            $table->string('completion_status', 40);
            $table->string('separation_reason', 40);
            $table->text('notes')->nullable();
            $table->date('effective_date');
            $table->string('status', 30)->default('approved');
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['separation_reason', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_separations');
        Schema::dropIfExists('record_attachments');
    }
};
