<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leads', 'phone')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->string('phone', 32)->nullable()->after('client_name')->index();
            });
        }

        if (! Schema::hasColumn('leads', 'email')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->string('email')->nullable()->after('phone');
            });
        }

        if (! Schema::hasColumn('leads', 'domicile')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->string('domicile')->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('leads', 'campaign')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->string('campaign')->nullable()->after('source');
            });
        }

        if (! Schema::hasColumn('leads', 'notes')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->text('notes')->nullable()->after('type');
            });
        }

        if (! Schema::hasColumn('leads', 'next_follow_up_at')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->timestamp('next_follow_up_at')->nullable()->after('notes')->index();
            });
        }

        if (! Schema::hasColumn('leads', 'last_contacted_at')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->timestamp('last_contacted_at')->nullable()->after('next_follow_up_at');
            });
        }

        if (! Schema::hasColumn('leads', 'first_response_at')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->timestamp('first_response_at')->nullable()->after('last_contacted_at');
            });
        }

        if (! Schema::hasColumn('leads', 'won_at')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->timestamp('won_at')->nullable()->after('first_response_at')->index();
            });
        }

        if (! Schema::hasColumn('leads', 'lost_reason')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->string('lost_reason')->nullable()->after('won_at');
            });
        }

        if (! Schema::hasColumn('leads', 'created_by')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->foreignId('created_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('client_inflows', 'lead_id')) {
            Schema::table('client_inflows', function (Blueprint $table): void {
                $table->foreignId('lead_id')->nullable()->after('id')->constrained('leads')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('lead_activities')) {
            Schema::create('lead_activities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type')->default('note');
                $table->string('channel')->default('internal');
                $table->string('direction')->default('internal');
                $table->text('body');
                $table->string('external_key')->nullable()->unique();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->useCurrent()->index();
                $table->timestamps();

                $table->index(['lead_id', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');

        if (Schema::hasColumn('client_inflows', 'lead_id')) {
            Schema::table('client_inflows', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('lead_id');
            });
        }

        if (Schema::hasColumn('leads', 'created_by')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('created_by');
            });
        }

        $columns = [
            'phone',
            'email',
            'domicile',
            'campaign',
            'notes',
            'next_follow_up_at',
            'last_contacted_at',
            'first_response_at',
            'won_at',
            'lost_reason',
        ];

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn('leads', $column),
        ));

        if ($existing !== []) {
            Schema::table('leads', function (Blueprint $table) use ($existing): void {
                $table->dropColumn($existing);
            });
        }
    }
};
