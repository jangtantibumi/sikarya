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
        if (!Schema::hasTable('client_inflows')) {
            Schema::create('client_inflows', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('client_name');
                $table->string('domicile')->nullable();
                $table->string('client_no')->nullable();
                $table->string('start_project')->nullable(); // Month e.g. Jan, Sep, Nov
                $table->string('package')->default('Bronze'); // Survey, Bronze, Silver, Gold, Diamond, Custom
                $table->text('notes')->nullable();
                $table->decimal('project_value', 15, 2)->default(0);
                $table->string('termin_no')->default('1'); // Survei, 1, 2, 3, 4
                $table->string('total_termin')->default('3'); // Survei, 3, 4
                $table->decimal('payment_amount', 15, 2)->default(0);
                $table->decimal('remaining_balance', 15, 2)->default(0);
                $table->string('payment_status')->default('Belum Lunas'); // LUNAS, Belum Lunas
                $table->string('invoice_file')->nullable();
                $table->string('pj_survey')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_inflows');
    }
};
