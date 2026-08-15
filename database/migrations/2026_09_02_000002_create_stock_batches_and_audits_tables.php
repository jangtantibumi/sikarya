<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expired_date')->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->timestamps();
            
            $table->index(['company_id', 'product_id', 'warehouse_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks')->nullOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
        });

        Schema::create('inventory_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft, completed
            $table->date('scheduled_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_audit_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_audit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('expected_qty', 15, 3)->default(0);
            $table->decimal('actual_qty', 15, 3)->default(0);
            $table->decimal('difference', 15, 3)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_audit_lines');
        Schema::dropIfExists('inventory_audits');
        
        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['rack_id']);
            $table->dropForeign(['bin_id']);
            $table->dropColumn(['batch_id', 'rack_id', 'bin_id']);
        });

        Schema::dropIfExists('stock_batches');
    }
};
