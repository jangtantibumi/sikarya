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
        // 1. Update tabel 'users' untuk menampung username, role, OTP, parent, contract
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->unique()->nullable()->after('email');
            });
        }
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('staff')->after('username');
            });
        }
        if (!Schema::hasColumn('users', 'parent')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('parent')->nullable()->after('role'); // Menyimpan username atasan langsung
            });
        }
        if (!Schema::hasColumn('users', 'otp_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('otp_code')->nullable()->after('password');
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            });
        }
        if (!Schema::hasColumn('users', 'employment_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employment_type')->default('Full-Time')->after('role'); // Full-Time, Part-Time, Paid/Unpaid Internship
            });
        }

        // 2. Update tabel 'leads'
        if (!Schema::hasColumn('leads', 'type')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('type')->nullable()->after('source');
            });
        }
        if (!Schema::hasColumn('leads', 'budget_text')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('budget_text')->nullable()->after('project_value');
            });
        }

        // 3. Update tabel 'attendances'
        if (!Schema::hasColumn('attendances', 'work_type')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('work_type')->default('WFO')->after('location_coordinates');
            });
        }
        if (!Schema::hasColumn('attendances', 'location_name')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('location_name')->nullable()->after('location_coordinates');
            });
        }

        // 4. Buat tabel 'rules' untuk engine aturan kerja
        if (!Schema::hasTable('rules')) {
            Schema::create('rules', function (Blueprint $table) {
                $table->id();
                $table->string('condition');
                $table->string('reward');
                $table->string('type')->default('success'); // success, warning, danger
                $table->timestamps();
            });
        }

        // 5. Buat tabel 'team_requests' untuk persetujuan penambahan/penghapusan tim
        if (!Schema::hasTable('team_requests')) {
            Schema::create('team_requests', function (Blueprint $table) {
                $table->id();
                $table->string('requester_username');
                $table->string('action'); // add, delete
                $table->string('target_username')->nullable();
                $table->text('details')->nullable(); // JSON data staf baru
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->timestamps();
            });
        }

        // 6. Buat tabel 'tasks' kustom jika belum ada
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('title');
                $table->string('status')->default('in_progress'); // in_progress, done
                $table->dateTime('deadline')->nullable();
                $table->string('relation')->nullable(); // target KPI
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('team_requests');
        Schema::dropIfExists('rules');

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn(['work_type', 'location_name']);
            });
        }

        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn(['type', 'budget_text']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['username', 'role', 'parent', 'otp_code', 'otp_expires_at', 'employment_type']);
            });
        }
    }
};
