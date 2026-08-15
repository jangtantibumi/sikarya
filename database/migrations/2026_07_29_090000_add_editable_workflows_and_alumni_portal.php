<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_status', 30)->default('active')->after('is_active')->index();
            $table->string('former_role', 100)->nullable()->after('role');
            $table->string('former_parent', 100)->nullable()->after('parent');
            $table->timestamp('alumni_since')->nullable()->after('deactivated_at');
        });

        Schema::table('employee_separations', function (Blueprint $table): void {
            $table->boolean('converted_to_alumni')->default(false)->after('completion_status');
        });

        Schema::create('alumni_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('former_role', 100)->nullable();
            $table->string('former_division', 60)->nullable()->index();
            $table->string('current_employer')->nullable();
            $table->string('current_position')->nullable();
            $table->string('industry')->nullable();
            $table->string('city')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->boolean('available_for_opportunities')->default(false);
            $table->boolean('receive_event_invitations')->default(true);
            $table->timestamp('last_profile_update_at')->nullable();
            $table->timestamps();
        });

        Schema::create('alumni_event_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->dateTime('event_at');
            $table->string('location')->nullable();
            $table->string('registration_url')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
        });

        Schema::create('alumni_invitation_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invitation_id')->constrained('alumni_event_invitations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['invitation_id', 'email']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_invitation_recipients');
        Schema::dropIfExists('alumni_event_invitations');
        Schema::dropIfExists('alumni_profiles');

        Schema::table('employee_separations', function (Blueprint $table): void {
            $table->dropColumn('converted_to_alumni');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['account_status']);
            $table->dropColumn([
                'account_status',
                'former_role',
                'former_parent',
                'alumni_since',
            ]);
        });
    }
};
