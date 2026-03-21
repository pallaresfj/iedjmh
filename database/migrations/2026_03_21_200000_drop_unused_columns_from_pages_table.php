<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dropTemplate = Schema::hasColumn('pages', 'template');
        $dropPublishedAt = Schema::hasColumn('pages', 'published_at');
        $dropSortOrder = Schema::hasColumn('pages', 'sort_order');

        if (! $dropTemplate && ! $dropPublishedAt && ! $dropSortOrder) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) use ($dropTemplate, $dropPublishedAt, $dropSortOrder): void {
            if ($dropTemplate) {
                $table->dropColumn('template');
            }

            if ($dropPublishedAt) {
                $table->dropColumn('published_at');
            }

            if ($dropSortOrder) {
                $table->dropColumn('sort_order');
            }
        });
    }

    public function down(): void
    {
        $addTemplate = ! Schema::hasColumn('pages', 'template');
        $addPublishedAt = ! Schema::hasColumn('pages', 'published_at');
        $addSortOrder = ! Schema::hasColumn('pages', 'sort_order');

        if (! $addTemplate && ! $addPublishedAt && ! $addSortOrder) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) use ($addTemplate, $addPublishedAt, $addSortOrder): void {
            if ($addTemplate) {
                $table->string('template')->nullable();
            }

            if ($addPublishedAt) {
                $table->timestamp('published_at')->nullable()->index();
            }

            if ($addSortOrder) {
                $table->unsignedInteger('sort_order')->default(0)->index();
            }
        });
    }
};
