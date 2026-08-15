<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry', 80)->nullable();
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->string('currency', 3)->default('IDR');
            $table->string('status', 20)->default('active')->index();
            $table->json('branding')->nullable();
            $table->timestamps();
        });

        Schema::create('company_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 80);
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('company_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('feature_key', 80);
            $table->string('state', 20)->default('off');
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'feature_key']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id');
            $table->foreign('company_id', 'users_company_id_fk')->references('id')->on('companies')->nullOnDelete();
        });

        foreach (['leads', 'tasks'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->foreignId('company_id')->nullable()->after('id');
                $table->foreign('company_id', $tableName . '_company_id_fk')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['leads', 'tasks'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropForeign($tableName . '_company_id_fk');
                $table->dropColumn('company_id');
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_company_id_fk');
            $table->dropColumn('company_id');
        });
        Schema::dropIfExists('company_features');
        Schema::dropIfExists('company_memberships');
        Schema::dropIfExists('companies');
    }
};
