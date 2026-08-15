<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('inv_settings');
        Schema::dropIfExists('inv_barcodes');
        Schema::dropIfExists('inv_batch_numbers');
        Schema::dropIfExists('inv_serial_numbers');
        Schema::dropIfExists('inv_delivery_lines');
        Schema::dropIfExists('inv_deliveries');
        Schema::dropIfExists('inv_packing_lines');
        Schema::dropIfExists('inv_packings');
        Schema::dropIfExists('inv_picking_lines');
        Schema::dropIfExists('inv_pickings');
        Schema::dropIfExists('inv_reservation_lines');
        Schema::dropIfExists('inv_reservations');
        Schema::dropIfExists('inv_cycle_count_lines');
        Schema::dropIfExists('inv_cycle_counts');
        Schema::dropIfExists('inv_adjustment_lines');
        Schema::dropIfExists('inv_adjustments');
        Schema::dropIfExists('inv_transfer_lines');
        Schema::dropIfExists('inv_transfers');
        Schema::dropIfExists('inv_stock_out_lines');
        Schema::dropIfExists('inv_stock_outs');
        Schema::dropIfExists('inv_stock_in_lines');
        Schema::dropIfExists('inv_stock_ins');
        Schema::dropIfExists('inv_stock_movements');
        Schema::dropIfExists('inv_stock_summaries');
        Schema::dropIfExists('inv_items');
        Schema::dropIfExists('inv_warehouse_bins');
        Schema::dropIfExists('inv_warehouse_racks');
        Schema::dropIfExists('inv_warehouse_zones');
        Schema::dropIfExists('inv_warehouses');
        Schema::dropIfExists('inv_uoms');
        Schema::dropIfExists('inv_brands');
        Schema::dropIfExists('inv_categories');

        Schema::enableForeignKeyConstraints();

        // 1. inv_categories
        Schema::create('inv_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. inv_brands
        Schema::create('inv_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. inv_uoms
        Schema::create('inv_uoms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('symbol')->nullable();
            $table->timestamps();
        });

        // 4. inv_warehouses
        Schema::create('inv_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. inv_warehouse_zones
        Schema::create('inv_warehouse_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 6. inv_warehouse_racks
        Schema::create('inv_warehouse_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('inv_warehouse_zones')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        // 7. inv_warehouse_bins
        Schema::create('inv_warehouse_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained('inv_warehouse_racks')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        // 8. inv_items
        Schema::create('inv_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('inv_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('inv_brands')->nullOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('inv_uoms')->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->integer('min_stock')->default(5);
            $table->integer('max_stock')->default(1000);
            $table->integer('reorder_point')->default(10);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 9. inv_stock_summaries
        Schema::create('inv_stock_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('reserved_qty', 15, 2)->default(0);
            $table->decimal('allocated_qty', 15, 2)->default(0);
            $table->timestamps();
        });

        // 10. inv_stock_movements (Stock Ledger)
        Schema::create('inv_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number');
            $table->string('transaction_type');
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('created_by')->default('System Admin');
            $table->timestamps();
        });

        // 11. inv_stock_ins
        Schema::create('inv_stock_ins', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->string('supplier_name')->nullable();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });

        // 12. inv_stock_in_lines
        Schema::create('inv_stock_in_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_id')->constrained('inv_stock_ins')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 13. inv_stock_outs
        Schema::create('inv_stock_outs', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->string('recipient_name')->nullable();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });

        // 14. inv_stock_out_lines
        Schema::create('inv_stock_out_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_out_id')->constrained('inv_stock_outs')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 15. inv_transfers
        Schema::create('inv_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('source_warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });

        // 16. inv_transfer_lines
        Schema::create('inv_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inv_transfers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('source_bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->foreignId('destination_bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 17. inv_adjustments
        Schema::create('inv_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('type')->default('addition');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });

        // 18. inv_adjustment_lines
        Schema::create('inv_adjustment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained('inv_adjustments')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('system_qty', 15, 2)->default(0);
            $table->decimal('actual_qty', 15, 2)->default(0);
            $table->decimal('adjustment_qty', 15, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 19. inv_cycle_counts
        Schema::create('inv_cycle_counts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->string('conducted_by')->nullable();
            $table->timestamps();
        });

        // 20. inv_cycle_count_lines
        Schema::create('inv_cycle_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_count_id')->constrained('inv_cycle_counts')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('expected_qty', 15, 2)->default(0);
            $table->decimal('counted_qty', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 21. inv_reservations
        Schema::create('inv_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->string('customer_name');
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 22. inv_reservation_lines
        Schema::create('inv_reservation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('inv_reservations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 23. inv_pickings
        Schema::create('inv_pickings', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('reservation_id')->nullable()->constrained('inv_reservations')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('picker_name')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 24. inv_picking_lines
        Schema::create('inv_picking_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_id')->constrained('inv_pickings')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('inv_warehouse_bins')->nullOnDelete();
            $table->decimal('requested_qty', 15, 2)->default(0);
            $table->decimal('picked_qty', 15, 2)->default(0);
            $table->timestamps();
        });

        // 25. inv_packings
        Schema::create('inv_packings', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('picking_id')->nullable()->constrained('inv_pickings')->nullOnDelete();
            $table->string('packer_name')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 26. inv_packing_lines
        Schema::create('inv_packing_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_id')->constrained('inv_packings')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->decimal('packed_qty', 15, 2)->default(0);
            $table->string('box_number')->nullable();
            $table->timestamps();
        });

        // 27. inv_deliveries
        Schema::create('inv_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('packing_id')->nullable()->constrained('inv_packings')->nullOnDelete();
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('draft');
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 28. inv_delivery_lines
        Schema::create('inv_delivery_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('inv_deliveries')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->decimal('delivered_qty', 15, 2)->default(0);
            $table->timestamps();
        });

        // 29. inv_serial_numbers
        Schema::create('inv_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('serial_number')->unique();
            $table->string('status')->default('available');
            $table->timestamps();
        });

        // 30. inv_batch_numbers
        Schema::create('inv_batch_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->timestamps();
        });

        // 31. inv_barcodes
        Schema::create('inv_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inv_items')->cascadeOnDelete();
            $table->string('barcode')->unique();
            $table->string('barcode_type')->default('CODE128');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });

        // 32. inv_settings
        Schema::create('inv_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inv_settings');
        Schema::dropIfExists('inv_barcodes');
        Schema::dropIfExists('inv_batch_numbers');
        Schema::dropIfExists('inv_serial_numbers');
        Schema::dropIfExists('inv_delivery_lines');
        Schema::dropIfExists('inv_deliveries');
        Schema::dropIfExists('inv_packing_lines');
        Schema::dropIfExists('inv_packings');
        Schema::dropIfExists('inv_picking_lines');
        Schema::dropIfExists('inv_pickings');
        Schema::dropIfExists('inv_reservation_lines');
        Schema::dropIfExists('inv_reservations');
        Schema::dropIfExists('inv_cycle_count_lines');
        Schema::dropIfExists('inv_cycle_counts');
        Schema::dropIfExists('inv_adjustment_lines');
        Schema::dropIfExists('inv_adjustments');
        Schema::dropIfExists('inv_transfer_lines');
        Schema::dropIfExists('inv_transfers');
        Schema::dropIfExists('inv_stock_out_lines');
        Schema::dropIfExists('inv_stock_outs');
        Schema::dropIfExists('inv_stock_in_lines');
        Schema::dropIfExists('inv_stock_ins');
        Schema::dropIfExists('inv_stock_movements');
        Schema::dropIfExists('inv_stock_summaries');
        Schema::dropIfExists('inv_items');
        Schema::dropIfExists('inv_warehouse_bins');
        Schema::dropIfExists('inv_warehouse_racks');
        Schema::dropIfExists('inv_warehouse_zones');
        Schema::dropIfExists('inv_warehouses');
        Schema::dropIfExists('inv_uoms');
        Schema::dropIfExists('inv_brands');
        Schema::dropIfExists('inv_categories');
        Schema::enableForeignKeyConstraints();
    }
};
