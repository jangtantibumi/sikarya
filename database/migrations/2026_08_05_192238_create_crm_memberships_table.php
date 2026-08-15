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
        Schema::create('crm_memberships', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. Guest, Silver, Gold
            $table->integer('min_points')->default(0); // Minimum points to reach this tier
            $table->decimal('discount_percentage', 5, 2)->default(0); // e.g. 5.00 for 5%
            $table->text('benefits')->nullable(); // description of benefits
            $table->string('color_badge')->default('gray'); // e.g. 'silver', 'gold' for CSS class
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_memberships');
    }
};
