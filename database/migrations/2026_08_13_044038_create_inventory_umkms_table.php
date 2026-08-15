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
        Schema::create('inventory_umkms', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable()->unique();
            $table->string('item_name');
            $table->string('category')->nullable();
            $table->string('uom')->nullable(); // e.g., gram, pcs
            $table->decimal('min_stock', 15, 2)->default(0);
            $table->decimal('max_stock', 15, 2)->nullable();
            $table->decimal('actual_stock', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('total_gram', 15, 2)->default(0);
            $table->decimal('price_per_gram', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_umkms');
    }
};
