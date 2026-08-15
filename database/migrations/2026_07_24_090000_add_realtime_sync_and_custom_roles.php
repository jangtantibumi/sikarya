<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index()->after('job_title');
            }
        });

        Schema::table('rules', function (Blueprint $table): void {
            if (!Schema::hasColumn('rules', 'division')) {
                $table->string('division')->nullable()->index()->after('type');
            }

            if (!Schema::hasColumn('rules', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('division')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table): void {
            if (Schema::hasColumn('rules', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('rules', 'division')) {
                $table->dropColumn('division');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['job_title', 'is_active'],
                fn (string $column): bool => Schema::hasColumn('users', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
