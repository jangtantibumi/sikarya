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
        Schema::create('crm_loyalties', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name'); // e.g. "Every Rp 10.000 = 1 Point"
            $table->decimal('spending_amount', 10, 2); // e.g. 10000
            $table->integer('points_awarded'); // e.g. 1
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_loyalties');
    }
};
