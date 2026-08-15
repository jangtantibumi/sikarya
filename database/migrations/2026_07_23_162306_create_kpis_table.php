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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_plan_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->decimal('target_value', 10, 2);
            $table->string('unit');
            $table->decimal('weight', 5, 2); // percentage weight
            $table->string('direction')->default('higher_is_better'); // higher_is_better, lower_is_better
            $table->string('aggregation_type')->default('sum'); // count, sum, average, percentage, manual
            $table->string('data_source')->default('tasks'); // tasks, leads, client_inflows, attendance
            $table->decimal('current_value', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
