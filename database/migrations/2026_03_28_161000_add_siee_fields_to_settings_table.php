<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'siee_name')) {
                $table->string('siee_name', 120)->nullable()->after('siee');
            }

            if (! Schema::hasColumn('settings', 'aula_virtual_name')) {
                $table->string('aula_virtual_name', 120)->nullable()->after('aula_virtual');
            }
        });

        if (! Schema::hasTable('documents') || Schema::hasColumn('settings', 'siee_document_id')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->foreignId('siee_document_id')
                ->nullable()
                ->after('aula_virtual')
                ->constrained('documents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (Schema::hasColumn('settings', 'siee_document_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('siee_document_id');
            });
        }

        Schema::table('settings', function (Blueprint $table): void {
            $columnsToDrop = array_filter([
                Schema::hasColumn('settings', 'siee_name') ? 'siee_name' : null,
                Schema::hasColumn('settings', 'aula_virtual_name') ? 'aula_virtual_name' : null,
            ]);

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
