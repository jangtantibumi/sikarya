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
        Schema::create('crm_customer_point_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('crm_customers')->onDelete('cascade');
            $table->integer('points'); // Positive for add, negative for redeem
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_customer_point_histories');
    }
};
