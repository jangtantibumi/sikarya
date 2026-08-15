<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Users Table
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_picture_path')->nullable();
        });

        // 2. Update Attendances Table
        Schema::table('attendances', function (Blueprint $table) {
            // 'morning' (Pagi), 'swing' (Middle), 'night' (Malam)
            $table->string('shift_type')->default('morning')->after('status');
        });

        // 3. Create Daily Reports Table
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->text('content');
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('submitted'); // submitted, locked
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });

        // 4. Create Leave Quotas Table
        Schema::create('leave_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('total_quota')->default(12);
            $table->integer('used_quota')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'year']);
        });

        // 5. Create Payslips Table
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month_year'); // e.g. "08-2026"
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->timestamps();

            $table->unique(['user_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('leave_quotas');
        Schema::dropIfExists('daily_reports');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('shift_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_picture_path');
        });
    }
};
