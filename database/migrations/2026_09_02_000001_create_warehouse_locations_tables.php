<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_racks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
            
            $table->unique(['warehouse_id', 'code']);
        });

        Schema::create('warehouse_bins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_rack_id')->constrained('warehouse_racks')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
            
            $table->unique(['warehouse_rack_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouse_racks');
    }
};
