<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('review_year');
            $table->string('review_cycle')->default('annual');
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->decimal('potential_score', 5, 2)->default(0);
            $table->decimal('competency_score', 5, 2)->default(0);
            $table->string('readiness')->default('developing');
            $table->string('status')->default('draft');
            $table->text('strengths')->nullable();
            $table->text('development_plan')->nullable();
            $table->string('next_role')->nullable();
            $table->json('training_plan')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'review_year', 'review_cycle']);
            $table->index(['review_year', 'status']);
        });

        Schema::create('erp_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->default('general');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->date('issued_at')->nullable();
            $table->json('content');
            $table->string('verification_token', 80)->unique();
            $table->string('document_hash', 64)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'status']);
        });

        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('erp_documents')->cascadeOnDelete();
            $table->foreignId('signer_id')->constrained('users')->cascadeOnDelete();
            $table->string('signer_role');
            $table->string('signature_method')->default('internal_authenticated_approval');
            $table->string('signature_hash', 64);
            $table->json('metadata')->nullable();
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->unique(['document_id', 'signer_id']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_inflow_id')->nullable()->constrained('client_inflows')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('client_name');
            $table->string('project_type')->default('design');
            $table->string('status')->default('active');
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->decimal('budget_cost', 15, 2)->default(0);
            $table->decimal('progress', 5, 2)->default(0);
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_type', 'status']);
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type');
            $table->string('normal_balance');
            $table->string('system_key')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('reference')->unique();
            $table->text('description');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status')->default('posted');
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('posted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['entry_date', 'status']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'project_id']);
        });

        Schema::create('project_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->date('cost_date');
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('vendor')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'cost_date']);
        });

        $now = now();
        DB::table('accounts')->insert([
            ['code' => '1100', 'name' => 'Kas & Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'cash_bank', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'accounts_receivable', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '2100', 'name' => 'Utang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'system_key' => 'accounts_payable', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '3100', 'name' => 'Modal & Saldo Laba', 'type' => 'equity', 'normal_balance' => 'credit', 'system_key' => 'retained_earnings', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4100', 'name' => 'Pendapatan Jasa Desain', 'type' => 'revenue', 'normal_balance' => 'credit', 'system_key' => 'design_revenue', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4200', 'name' => 'Pendapatan Kontraktor', 'type' => 'revenue', 'normal_balance' => 'credit', 'system_key' => 'contractor_revenue', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5100', 'name' => 'Biaya Langsung Proyek', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'direct_project_cost', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '6100', 'name' => 'Beban Gaji & SDM', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'payroll_expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '6200', 'name' => 'Beban Operasional', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'operating_expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '6300', 'name' => 'Beban Pemasaran', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'marketing_expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_costs');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('erp_documents');
        Schema::dropIfExists('talent_reviews');
    }
};
