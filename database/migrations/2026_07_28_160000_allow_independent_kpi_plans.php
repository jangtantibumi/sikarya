<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_plans', function (Blueprint $table): void {
            $table->string('title')->nullable()->after('goal_id');
            $table->string('division', 50)->nullable()->after('title')->index();
        });

        DB::table('kpi_plans')
            ->join('goals', 'goals.id', '=', 'kpi_plans.goal_id')
            ->select('kpi_plans.id', 'goals.title', 'goals.division')
            ->orderBy('kpi_plans.id')
            ->each(function ($plan): void {
                DB::table('kpi_plans')
                    ->where('id', $plan->id)
                    ->update([
                        'title' => $plan->title,
                        'division' => $plan->division,
                    ]);
            });

        Schema::table('kpi_plans', function (Blueprint $table): void {
            $table->foreignId('goal_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('kpi_plans')->whereNull('goal_id')->exists()) {
            throw new \RuntimeException(
                'Rollback dibatalkan: terdapat rencana KPI mandiri yang tidak memiliki goal CEO.'
            );
        }

        Schema::table('kpi_plans', function (Blueprint $table): void {
            $table->foreignId('goal_id')->nullable(false)->change();
            $table->dropIndex(['division']);
            $table->dropColumn(['title', 'division']);
        });
    }
};
