<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('settings') || Schema::hasColumn('settings', 'location')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->string('location')->nullable()->after('nit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'location')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('location');
        });
    }
};
