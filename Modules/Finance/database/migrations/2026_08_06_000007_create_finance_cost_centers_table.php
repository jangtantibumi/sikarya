<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_cost_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->uuid('parent_id')->nullable();
            $table->string('manager_name', 150)->nullable();
            $table->string('department', 100)->nullable();
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'unq_fin_cc_company_code');
            $table->foreign('parent_id')->references('id')->on('finance_cost_centers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_cost_centers');
    }
};
