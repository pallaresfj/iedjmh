<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->foreignId('pei_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('manual_convivencia_document_id')->nullable()->constrained('documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('pei_document_id');
            $table->dropConstrainedForeignId('manual_convivencia_document_id');
        });
    }
};
