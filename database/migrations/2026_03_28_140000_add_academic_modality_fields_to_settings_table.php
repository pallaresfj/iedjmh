<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'academic_modality_label')) {
                $table->string('academic_modality_label', 120)->nullable()->after('nit');
            }

            if (! Schema::hasColumn('settings', 'academic_modality_icon')) {
                $table->string('academic_modality_icon', 60)->nullable()->after('academic_modality_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $columnsToDrop = array_filter([
                Schema::hasColumn('settings', 'academic_modality_icon') ? 'academic_modality_icon' : null,
                Schema::hasColumn('settings', 'academic_modality_label') ? 'academic_modality_label' : null,
            ]);

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
