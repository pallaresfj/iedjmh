<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || Schema::hasColumn('settings', 'allies')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->json('allies')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'allies')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('allies');
        });
    }
};
