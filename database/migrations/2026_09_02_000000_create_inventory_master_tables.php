<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'name']);
            });
        }

        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'name']);
            });
        }

        if (Schema::hasTable('products')) {
            if (! Schema::hasColumn('products', 'category_id')) {
                Schema::table('products', function (Blueprint $table): void {
                    $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                    $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
                    $table->string('barcode')->nullable();
                    $table->string('qr_code')->nullable();
                    $table->boolean('has_batches')->default(false);
                    $table->boolean('has_serial_numbers')->default(false);
                    
                    $table->unique(['company_id', 'barcode']);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropUnique(['company_id', 'barcode']);
            $table->dropColumn(['category_id', 'brand_id', 'barcode', 'qr_code', 'has_batches', 'has_serial_numbers']);
        });

        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
