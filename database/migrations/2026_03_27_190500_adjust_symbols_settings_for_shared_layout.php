<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $legacyColumns = [
        'symbols_hero_eyebrow',
        'symbols_hero_title',
        'symbols_hero_description',
        'symbols_hero_image_path',
        'symbols_hymn_chorus',
        'symbols_hymn_verse_one',
        'symbols_hymn_verse_two',
        'symbols_hymn_verse_three',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (! Schema::hasColumn('settings', 'symbols_hymn_lyrics')) {
            Schema::table('settings', function (Blueprint $table): void {
                if (Schema::hasColumn('settings', 'symbols_hymn_audio_path')) {
                    $table->text('symbols_hymn_lyrics')->nullable()->after('symbols_hymn_audio_path');

                    return;
                }

                $table->text('symbols_hymn_lyrics')->nullable();
            });
        }

        $this->migrateLegacyHymnFieldsToLyrics();
        $this->dropLegacyColumns();
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'symbols_hero_eyebrow')) {
                $table->string('symbols_hero_eyebrow', 120)->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hero_title')) {
                $table->string('symbols_hero_title', 160)->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hero_description')) {
                $table->text('symbols_hero_description')->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hero_image_path')) {
                $table->string('symbols_hero_image_path')->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_chorus')) {
                $table->text('symbols_hymn_chorus')->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_verse_one')) {
                $table->text('symbols_hymn_verse_one')->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_verse_two')) {
                $table->text('symbols_hymn_verse_two')->nullable();
            }

            if (! Schema::hasColumn('settings', 'symbols_hymn_verse_three')) {
                $table->text('symbols_hymn_verse_three')->nullable();
            }
        });

        if (Schema::hasColumn('settings', 'symbols_hymn_lyrics') && Schema::hasColumn('settings', 'symbols_hymn_chorus')) {
            DB::table('settings')
                ->orderBy('id')
                ->select(['id', 'symbols_hymn_lyrics', 'symbols_hymn_chorus'])
                ->get()
                ->each(function (object $setting): void {
                    $existingChorus = trim((string) ($setting->symbols_hymn_chorus ?? ''));
                    $lyrics = trim((string) ($setting->symbols_hymn_lyrics ?? ''));

                    if ($existingChorus === '' && $lyrics !== '') {
                        DB::table('settings')
                            ->where('id', $setting->id)
                            ->update(['symbols_hymn_chorus' => $lyrics]);
                    }
                });
        }

        if (Schema::hasColumn('settings', 'symbols_hymn_lyrics')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropColumn('symbols_hymn_lyrics');
            });
        }
    }

    private function migrateLegacyHymnFieldsToLyrics(): void
    {
        if (! Schema::hasColumn('settings', 'symbols_hymn_lyrics')) {
            return;
        }

        $legacyHymnColumns = [
            'symbols_hymn_chorus',
            'symbols_hymn_verse_one',
            'symbols_hymn_verse_two',
            'symbols_hymn_verse_three',
        ];

        $availableLegacyColumns = array_values(array_filter(
            $legacyHymnColumns,
            fn (string $column): bool => Schema::hasColumn('settings', $column)
        ));

        if ($availableLegacyColumns === []) {
            return;
        }

        $selectColumns = array_merge(['id', 'symbols_hymn_lyrics'], $availableLegacyColumns);

        DB::table('settings')
            ->orderBy('id')
            ->select($selectColumns)
            ->get()
            ->each(function (object $setting): void {
                $currentLyrics = trim((string) ($setting->symbols_hymn_lyrics ?? ''));

                if ($currentLyrics !== '') {
                    return;
                }

                $mergedLyrics = $this->composeLyricsFromLegacyFields($setting);

                if ($mergedLyrics === null) {
                    return;
                }

                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update(['symbols_hymn_lyrics' => $mergedLyrics]);
            });
    }

    private function composeLyricsFromLegacyFields(object $setting): ?string
    {
        $sections = [];

        $legacyParts = [
            'Coro' => $setting->symbols_hymn_chorus ?? null,
            'Estrofa I' => $setting->symbols_hymn_verse_one ?? null,
            'Estrofa II' => $setting->symbols_hymn_verse_two ?? null,
            'Estrofa III' => $setting->symbols_hymn_verse_three ?? null,
        ];

        foreach ($legacyParts as $label => $value) {
            $content = trim((string) ($value ?? ''));

            if ($content === '') {
                continue;
            }

            $sections[] = $label."\n".$content;
        }

        if ($sections === []) {
            return null;
        }

        return implode("\n\n", $sections);
    }

    private function dropLegacyColumns(): void
    {
        $columnsToDrop = array_values(array_filter(
            $this->legacyColumns,
            fn (string $column): bool => Schema::hasColumn('settings', $column)
        ));

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }
};
