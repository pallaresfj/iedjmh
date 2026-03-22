<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('banners') || ! Schema::hasColumn('banners', 'sort_order')) {
            return;
        }

        Schema::table('banners', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('banners') || Schema::hasColumn('banners', 'sort_order')) {
            return;
        }

        Schema::table('banners', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('ends_at')->index();
        });
    }
};
