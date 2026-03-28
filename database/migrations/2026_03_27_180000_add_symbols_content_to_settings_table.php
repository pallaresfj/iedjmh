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
        'symbols_hero_eyebrow',
        'symbols_hero_title',
        'symbols_hero_description',
        'symbols_hero_image_path',
        'symbols_flag_intro',
        'symbols_flag_stripes',
        'symbols_shield_intro',
        'symbols_shield_items',
        'symbols_hymn_title',
        'symbols_hymn_audio_path',
        'symbols_hymn_chorus',
        'symbols_hymn_verse_one',
        'symbols_hymn_verse_two',
        'symbols_hymn_verse_three',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings') || $this->allColumnsExist()) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'symbols_hero_eyebrow')) {
                $table->string('symbols_hero_eyebrow', 120)->nullable()->after('home_hero_image_path');
            }

            if (! Schema::hasColumn('settings', 'symbols_hero_title')) {
                $table->string('symbols_hero_title', 160)->nullable()->after('symbols_hero_eyebrow');
            }

            if (! Schema::hasColumn('settings', 'symbols_hero_description')) {
                $table->text('symbols_hero_description')->nullable()->after('symbols_hero_title');
            }

            if (! Schema::hasColumn('settings', 'symbols_hero_image_path')) {
                $table->string('symbols_hero_image_path')->nullable()->after('symbols_hero_description');
            }

            if (! Schema::hasColumn('settings', 'symbols_flag_intro')) {
                $table->text('symbols_flag_intro')->nullable()->after('symbols_hero_image_path');
            }

            if (! Schema::hasColumn('settings', 'symbols_flag_stripes')) {
                $table->json('symbols_flag_stripes')->nullable()->after('symbols_flag_intro');
            }

            if (! Schema::hasColumn('settings', 'symbols_shield_intro')) {
                $table->text('symbols_shield_intro')->nullable()->after('symbols_flag_stripes');
            }

            if (! Schema::hasColumn('settings', 'symbols_shield_items')) {
                $table->json('symbols_shield_items')->nullable()->after('symbols_shield_intro');
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_title')) {
                $table->string('symbols_hymn_title', 160)->nullable()->after('symbols_shield_items');
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_audio_path')) {
                $table->string('symbols_hymn_audio_path')->nullable()->after('symbols_hymn_title');
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_chorus')) {
                $table->text('symbols_hymn_chorus')->nullable()->after('symbols_hymn_audio_path');
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_verse_one')) {
                $table->text('symbols_hymn_verse_one')->nullable()->after('symbols_hymn_chorus');
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_verse_two')) {
                $table->text('symbols_hymn_verse_two')->nullable()->after('symbols_hymn_verse_one');
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_verse_three')) {
                $table->text('symbols_hymn_verse_three')->nullable()->after('symbols_hymn_verse_two');
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
