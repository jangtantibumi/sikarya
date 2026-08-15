<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_event_invitations', function (Blueprint $table): void {
            $table->string('division', 60)->nullable()->index()->after('created_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_event_invitations', function (Blueprint $table): void {
            $table->dropIndex(['division']);
            $table->dropColumn('division');
        });
    }
};
