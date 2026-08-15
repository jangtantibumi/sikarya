<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('account_group_id')->nullable();
            $table->string('code', 50);
            $table->string('name', 200);
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('balance_type', ['debit', 'credit']);
            $table->uuid('parent_id')->nullable();
            $table->uuid('currency_id')->nullable();
            $table->boolean('is_header')->default(false);
            $table->boolean('is_reconciliation')->default(false);
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'unq_fin_coa_company_code');
            $table->foreign('account_group_id')->references('id')->on('finance_account_groups')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('finance_chart_of_accounts')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('finance_currencies')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_chart_of_accounts');
    }
};
