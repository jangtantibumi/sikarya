<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_fiscal_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('fiscal_year_id');
            $table->integer('period_number');
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['fiscal_year_id', 'period_number'], 'unq_fin_fp_fy_period');
            $table->foreign('fiscal_year_id')->references('id')->on('finance_fiscal_years')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_fiscal_periods');
    }
};
