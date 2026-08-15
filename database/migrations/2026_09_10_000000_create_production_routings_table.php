<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_routings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('bill_of_material_id')->nullable()->constrained('bill_of_materials')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('production_routing_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_routing_id')->constrained('production_routings')->cascadeOnDelete();
            $table->integer('sequence');
            $table->string('work_center');
            $table->decimal('expected_duration_minutes', 8, 2)->default(0);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_routing_steps');
        Schema::dropIfExists('production_routings');
    }
};
