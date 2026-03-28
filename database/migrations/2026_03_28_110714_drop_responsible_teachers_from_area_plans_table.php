<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('area_plans', 'responsible_teachers')) {
            return;
        }

        Schema::table('area_plans', function (Blueprint $table): void {
            $table->dropColumn('responsible_teachers');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('area_plans', 'responsible_teachers')) {
            return;
        }

        Schema::table('area_plans', function (Blueprint $table): void {
            $table->text('responsible_teachers')->nullable()->after('area_name');
        });
    }
};
