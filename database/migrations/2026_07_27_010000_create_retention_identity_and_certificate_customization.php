<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_code', 40)->nullable()->unique()->after('username');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            $table->timestamp('archived_at')->nullable()->after('deactivated_at');
            $table->timestamp('anonymized_at')->nullable()->after('archived_at');
            $table->boolean('legal_hold')->default(false)->after('anonymized_at');
            $table->string('signature_image_path')->nullable()->after('legal_hold');
            $table->timestamp('signature_consented_at')->nullable()->after('signature_image_path');
        });

        DB::table('users')
            ->orderBy('id')
            ->get(['id', 'role', 'employment_type', 'is_active', 'updated_at'])
            ->each(function (object $user): void {
                $division = match (true) {
                    str_contains((string) $user->role, 'marketing') => 'MKT',
                    str_contains((string) $user->role, 'ops') => 'OPS',
                    str_contains((string) $user->role, 'finance') => 'FIN',
                    str_contains((string) $user->role, 'hrd') => 'HRD',
                    $user->role === 'ceo' => 'EXE',
                    default => 'GEN',
                };
                $level = match (true) {
                    str_contains(strtolower((string) $user->employment_type), 'intern') => 'INT',
                    $user->role === 'ceo' => 'CEO',
                    str_starts_with((string) $user->role, 'mgr_') => 'MGR',
                    default => 'STF',
                };

                DB::table('users')->where('id', $user->id)->update([
                    'employee_code' => sprintf(
                        'SA-%s-%s-%04d',
                        $division,
                        $level,
                        $user->id,
                    ),
                    'deactivated_at' => $user->is_active ? null : $user->updated_at,
                ]);
            });

        Schema::create('certificate_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('background_path');
            $table->string('background_mime', 80);
            $table->string('file_hash', 64);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'created_at']);
        });

        Schema::table('erp_documents', function (Blueprint $table): void {
            $table->foreignId('certificate_template_id')
                ->nullable()
                ->after('created_by_id')
                ->constrained('certificate_templates')
                ->nullOnDelete();
            $table->foreignId('supervisor_user_id')
                ->nullable()
                ->after('certificate_template_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('document_signatures', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('signature_hash');
        });

        Schema::create('retention_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('mode')->default('scheduled');
            $table->foreignId('ran_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metrics');
            $table->timestamp('completed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_runs');

        Schema::table('document_signatures', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });

        Schema::table('erp_documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('supervisor_user_id');
            $table->dropConstrainedForeignId('certificate_template_id');
        });

        Schema::dropIfExists('certificate_templates');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['employee_code']);
            $table->dropColumn([
                'employee_code',
                'deactivated_at',
                'archived_at',
                'anonymized_at',
                'legal_hold',
                'signature_image_path',
                'signature_consented_at',
            ]);
        });
    }
};
