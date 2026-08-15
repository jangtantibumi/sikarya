<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('erp_documents', function (Blueprint $table): void { $table->dropUnique(['document_number']); $table->unique(['company_id','document_number'], 'erp_documents_company_document_number_unique'); }); }
    public function down(): void { Schema::table('erp_documents', function (Blueprint $table): void { $table->dropUnique('erp_documents_company_document_number_unique'); $table->unique('document_number'); }); }
};
