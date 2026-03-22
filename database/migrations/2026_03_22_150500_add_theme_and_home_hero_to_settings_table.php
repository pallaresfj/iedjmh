<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $columns = [
        'theme_primary',
        'theme_primary_dark',
        'theme_primary_light',
        'theme_accent',
        'theme_gray_900',
        'theme_gray_700',
        'theme_gray_600',
        'theme_gray_200',
        'theme_gray_100',
        'home_hero_eyebrow',
        'home_hero_title',
        'home_hero_description',
        'home_hero_cta_label',
        'home_hero_cta_url',
        'home_hero_cta_target',
        'home_hero_image_path',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings') || $this->allColumnsExist()) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'theme_primary')) {
                $table->string('theme_primary', 7)->nullable()->after('logo_path');
            }

            if (! Schema::hasColumn('settings', 'theme_primary_dark')) {
                $table->string('theme_primary_dark', 7)->nullable()->after('theme_primary');
            }

            if (! Schema::hasColumn('settings', 'theme_primary_light')) {
                $table->string('theme_primary_light', 7)->nullable()->after('theme_primary_dark');
            }

            if (! Schema::hasColumn('settings', 'theme_accent')) {
                $table->string('theme_accent', 7)->nullable()->after('theme_primary_light');
            }

            if (! Schema::hasColumn('settings', 'theme_gray_900')) {
                $table->string('theme_gray_900', 7)->nullable()->after('theme_accent');
            }

            if (! Schema::hasColumn('settings', 'theme_gray_700')) {
                $table->string('theme_gray_700', 7)->nullable()->after('theme_gray_900');
            }

            if (! Schema::hasColumn('settings', 'theme_gray_600')) {
                $table->string('theme_gray_600', 7)->nullable()->after('theme_gray_700');
            }

            if (! Schema::hasColumn('settings', 'theme_gray_200')) {
                $table->string('theme_gray_200', 7)->nullable()->after('theme_gray_600');
            }

            if (! Schema::hasColumn('settings', 'theme_gray_100')) {
                $table->string('theme_gray_100', 7)->nullable()->after('theme_gray_200');
            }

            if (! Schema::hasColumn('settings', 'home_hero_eyebrow')) {
                $table->string('home_hero_eyebrow')->nullable()->after('theme_gray_100');
            }

            if (! Schema::hasColumn('settings', 'home_hero_title')) {
                $table->string('home_hero_title')->nullable()->after('home_hero_eyebrow');
            }

            if (! Schema::hasColumn('settings', 'home_hero_description')) {
                $table->text('home_hero_description')->nullable()->after('home_hero_title');
            }

            if (! Schema::hasColumn('settings', 'home_hero_cta_label')) {
                $table->string('home_hero_cta_label', 100)->nullable()->after('home_hero_description');
            }

            if (! Schema::hasColumn('settings', 'home_hero_cta_url')) {
                $table->string('home_hero_cta_url', 2048)->nullable()->after('home_hero_cta_label');
            }

            if (! Schema::hasColumn('settings', 'home_hero_cta_target')) {
                $table->string('home_hero_cta_target', 10)->nullable()->after('home_hero_cta_url');
            }

            if (! Schema::hasColumn('settings', 'home_hero_image_path')) {
                $table->string('home_hero_image_path')->nullable()->after('home_hero_cta_target');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function allColumnsExist(): bool
    {
        foreach ($this->columns as $column) {
            if (! Schema::hasColumn('settings', $column)) {
                return false;
            }
        }

        return true;
    }
};
