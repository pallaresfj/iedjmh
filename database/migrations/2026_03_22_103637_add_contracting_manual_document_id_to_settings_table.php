<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || Schema::hasColumn('settings', 'contracting_manual_document_id')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->foreignId('contracting_manual_document_id')
                ->nullable()
                ->after('logo_path')
                ->constrained('documents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'contracting_manual_document_id')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('contracting_manual_document_id');
        });
    }
};
