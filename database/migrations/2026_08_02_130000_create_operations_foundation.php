<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code'); $table->string('name'); $table->string('location')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['company_id', 'code']);
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('sku'); $table->string('name'); $table->string('unit')->default('pcs'); $table->decimal('reorder_level', 15, 3)->default(0); $table->decimal('standard_cost', 18, 2)->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['company_id', 'sku']);
        });
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('type'); $table->decimal('quantity', 15, 3); $table->decimal('unit_cost', 18, 2)->default(0); $table->string('reference')->nullable(); $table->text('notes')->nullable(); $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->index(['company_id', 'product_id', 'warehouse_id']);
        });
        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete(); $table->string('number'); $table->string('title'); $table->string('status')->default('draft'); $table->text('reason')->nullable(); $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->unique(['company_id', 'number']);
        });
        Schema::create('production_orders', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete(); $table->string('number'); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->decimal('planned_quantity', 15, 3); $table->decimal('completed_quantity', 15, 3)->default(0); $table->string('status')->default('draft'); $table->date('planned_date')->nullable(); $table->timestamps(); $table->unique(['company_id', 'number']);
        });
    }
    public function down(): void { Schema::dropIfExists('production_orders'); Schema::dropIfExists('purchase_requests'); Schema::dropIfExists('stock_movements'); Schema::dropIfExists('products'); Schema::dropIfExists('warehouses'); }
};
