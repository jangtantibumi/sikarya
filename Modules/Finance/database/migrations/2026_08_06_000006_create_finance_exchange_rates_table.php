<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('from_currency_id');
            $table->uuid('to_currency_id');
            $table->date('rate_date');
            $table->enum('rate_type', ['spot', 'monthly', 'corporate', 'tax'])->default('spot');
            $table->decimal('rate', 20, 6);
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('from_currency_id')->references('id')->on('finance_currencies')->onDelete('cascade');
            $table->foreign('to_currency_id')->references('id')->on('finance_currencies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_exchange_rates');
    }
};
