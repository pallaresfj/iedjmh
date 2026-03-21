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
        if (! Schema::hasTable('posts')) {
            return;
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('posts', 'seo_title') ? 'seo_title' : null,
            Schema::hasColumn('posts', 'seo_description') ? 'seo_description' : null,
            Schema::hasColumn('posts', 'seo_image_path') ? 'seo_image_path' : null,
        ]));

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        $missingSeoTitle = ! Schema::hasColumn('posts', 'seo_title');
        $missingSeoDescription = ! Schema::hasColumn('posts', 'seo_description');
        $missingSeoImagePath = ! Schema::hasColumn('posts', 'seo_image_path');

        if (! $missingSeoTitle && ! $missingSeoDescription && ! $missingSeoImagePath) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) use ($missingSeoTitle, $missingSeoDescription, $missingSeoImagePath): void {
            if ($missingSeoTitle) {
                $table->string('seo_title')->nullable();
            }

            if ($missingSeoDescription) {
                $table->text('seo_description')->nullable();
            }

            if ($missingSeoImagePath) {
                $table->string('seo_image_path')->nullable();
            }
        });
    }
};
