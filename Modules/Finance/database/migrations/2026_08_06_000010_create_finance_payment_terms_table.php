<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_payment_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->integer('net_days')->default(0);
            $table->integer('discount_days')->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'unq_fin_pt_company_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payment_terms');
    }
};
