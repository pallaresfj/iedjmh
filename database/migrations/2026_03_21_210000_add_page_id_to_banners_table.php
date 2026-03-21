<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('banners') || Schema::hasColumn('banners', 'page_id')) {
            return;
        }

        Schema::table('banners', function (Blueprint $table): void {
            $table->foreignId('page_id')
                ->nullable()
                ->after('slug')
                ->constrained('pages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('banners') || ! Schema::hasColumn('banners', 'page_id')) {
            return;
        }

        Schema::table('banners', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('page_id');
        });
    }
};
