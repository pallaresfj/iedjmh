<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || Schema::hasColumn('settings', 'contact_hours')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->string('contact_hours')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'contact_hours')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('contact_hours');
        });
    }
};
