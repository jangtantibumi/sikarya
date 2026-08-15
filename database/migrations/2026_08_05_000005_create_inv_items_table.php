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
        Schema::create('inv_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('base_uom_id');
            $table->enum('type', ['product', 'service', 'consumable'])->default('product');
            $table->enum('cost_method', ['fifo', 'average', 'standard'])->default('average');
            $table->enum('tracking_type', ['none', 'serial', 'batch'])->default('none');
            $table->decimal('reorder_level', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('category_id')->references('id')->on('inv_categories')->onDelete('restrict');
            $table->foreign('brand_id')->references('id')->on('inv_brands')->onDelete('restrict');
            $table->foreign('base_uom_id')->references('id')->on('inv_uoms')->onDelete('restrict');
            
            // Indexes for performance
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_items');
    }
};
