<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || Schema::hasColumn('settings', 'symbols_shield_image_path')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (Schema::hasColumn('settings', 'symbols_shield_intro')) {
                $table->string('symbols_shield_image_path')->nullable()->after('symbols_shield_intro');

                return;
            }

            $table->string('symbols_shield_image_path')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'symbols_shield_image_path')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('symbols_shield_image_path');
        });
    }
};
