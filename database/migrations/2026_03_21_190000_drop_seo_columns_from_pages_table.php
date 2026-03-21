<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dropSeoTitle = Schema::hasColumn('pages', 'seo_title');
        $dropSeoDescription = Schema::hasColumn('pages', 'seo_description');
        $dropSeoImagePath = Schema::hasColumn('pages', 'seo_image_path');

        if (! $dropSeoTitle && ! $dropSeoDescription && ! $dropSeoImagePath) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) use ($dropSeoTitle, $dropSeoDescription, $dropSeoImagePath): void {
            if ($dropSeoTitle) {
                $table->dropColumn('seo_title');
            }

            if ($dropSeoDescription) {
                $table->dropColumn('seo_description');
            }

            if ($dropSeoImagePath) {
                $table->dropColumn('seo_image_path');
            }
        });
    }

    public function down(): void
    {
        $addSeoTitle = ! Schema::hasColumn('pages', 'seo_title');
        $addSeoDescription = ! Schema::hasColumn('pages', 'seo_description');
        $addSeoImagePath = ! Schema::hasColumn('pages', 'seo_image_path');

        if (! $addSeoTitle && ! $addSeoDescription && ! $addSeoImagePath) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) use ($addSeoTitle, $addSeoDescription, $addSeoImagePath): void {
            if ($addSeoTitle) {
                $table->string('seo_title')->nullable();
            }

            if ($addSeoDescription) {
                $table->text('seo_description')->nullable();
            }

            if ($addSeoImagePath) {
                $table->string('seo_image_path')->nullable();
            }
        });
    }
};
