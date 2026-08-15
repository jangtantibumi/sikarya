<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_numbering_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->string('module_code', 50);
            $table->string('document_type', 50);
            $table->string('prefix', 50);
            $table->string('suffix', 50)->nullable();
            $table->integer('length')->default(5);
            $table->integer('current_number')->default(0);
            $table->enum('reset_cycle', ['never', 'yearly', 'monthly', 'daily'])->default('yearly');
            $table->date('last_reset_date')->nullable();
            $table->string('sample_number', 100)->nullable();
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'module_code', 'document_type'], 'unq_fin_ns_company_mod_doc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_numbering_series');
    }
};
