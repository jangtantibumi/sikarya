<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_account_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->enum('category', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->string('code_from', 50)->nullable();
            $table->string('code_to', 50)->nullable();
            $table->enum('report_type', ['balance_sheet', 'profit_loss']);
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'unq_fin_acc_grp_company_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_account_groups');
    }
};
