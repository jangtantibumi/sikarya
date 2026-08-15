<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // e.g., 'SP', 'Paklaring', 'Etos Kerja', 'Lainnya'
            $table->string('file_path');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Uploaded by
            $table->foreignId('target_user_id')->nullable()->constrained('users')->cascadeOnDelete(); // If null, means for all employees
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_documents');
    }
};
