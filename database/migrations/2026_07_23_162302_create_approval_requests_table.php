<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_type'); // leave, team_request, kpi_plan
            $table->string('division')->nullable();
            $table->foreignId('requester_id')->constrained('users');
            $table->nullableMorphs('subject'); // subject_type, subject_id
            $table->foreignId('current_approver_id')->nullable()->constrained('users');
            $table->integer('current_step')->default(1);
            $table->string('status')->default('draft'); // draft, pending_manager, pending_ceo, approved, rejected, cancelled
            $table->json('payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
