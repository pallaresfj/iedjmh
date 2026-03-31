<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyRow = null;

        if (Schema::hasTable('settings')) {
            $legacyRow = DB::table('settings')->orderBy('id')->first();

            Schema::disableForeignKeyConstraints();
            Schema::drop('settings');
            Schema::enableForeignKeyConstraints();
        }

        $this->createCurrentSettingsSchema();

        DB::table('settings')->insert(
            $this->buildCurrentRowPayload(
                is_object($legacyRow) ? (array) $legacyRow : [],
            ),
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::disableForeignKeyConstraints();
            Schema::drop('settings');
            Schema::enableForeignKeyConstraints();
        }

        $this->createLegacySettingsSchema();
    }

    private function createCurrentSettingsSchema(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('singleton')->default(1)->index();
            $table->string('institution_name');
            $table->string('dane', 100)->nullable();
            $table->string('nit', 100)->nullable();
            $table->string('location')->nullable();
            $table->string('academic_modality_label', 120)->nullable();
            $table->string('academic_modality_icon', 60)->nullable();
            $table->string('rector_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 80)->nullable();
            $table->string('address')->nullable();
            $table->string('siee')->nullable();
            $table->string('siee_name', 120)->nullable();
            $table->foreignId('siee_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('aula_virtual')->nullable();
            $table->string('aula_virtual_name', 120)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('theme_primary', 7)->nullable();
            $table->string('theme_primary_dark', 7)->nullable();
            $table->string('theme_primary_light', 7)->nullable();
            $table->string('theme_accent', 7)->nullable();
            $table->string('theme_gray_900', 7)->nullable();
            $table->string('theme_gray_700', 7)->nullable();
            $table->string('theme_gray_600', 7)->nullable();
            $table->string('theme_gray_200', 7)->nullable();
            $table->string('theme_gray_100', 7)->nullable();
            $table->string('home_hero_eyebrow')->nullable();
            $table->string('home_hero_title')->nullable();
            $table->text('home_hero_description')->nullable();
            $table->string('home_hero_cta_label', 100)->nullable();
            $table->string('home_hero_cta_url', 2048)->nullable();
            $table->string('home_hero_cta_target', 10)->nullable();
            $table->string('home_hero_image_path')->nullable();
            $table->json('allies')->nullable();
            $table->text('contact_hours')->nullable();
            $table->decimal('location_latitude', 10, 7)->nullable();
            $table->decimal('location_longitude', 10, 7)->nullable();
            $table->text('symbols_flag_intro')->nullable();
            $table->json('symbols_flag_stripes')->nullable();
            $table->text('symbols_shield_intro')->nullable();
            $table->string('symbols_shield_image_path')->nullable();
            $table->json('symbols_shield_items')->nullable();
            $table->string('symbols_hymn_title', 160)->nullable();
            $table->string('symbols_hymn_audio_path')->nullable();
            $table->longText('symbols_hymn_lyrics')->nullable();
            $table->foreignId('contracting_manual_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });
    }

    private function createLegacySettingsSchema(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name');
            $table->string('dane_code', 50)->nullable();
            $table->string('nit', 50)->nullable();
            $table->string('location')->nullable();
            $table->string('academic_modality_label', 120)->nullable();
            $table->string('academic_modality_icon', 60)->nullable();
            $table->string('rector_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('siee')->nullable();
            $table->string('siee_name', 120)->nullable();
            $table->foreignId('siee_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('aula_virtual')->nullable();
            $table->string('aula_virtual_name', 120)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('theme_primary', 7)->nullable();
            $table->string('theme_primary_dark', 7)->nullable();
            $table->string('theme_primary_light', 7)->nullable();
            $table->string('theme_accent', 7)->nullable();
            $table->string('theme_gray_900', 7)->nullable();
            $table->string('theme_gray_700', 7)->nullable();
            $table->string('theme_gray_600', 7)->nullable();
            $table->string('theme_gray_200', 7)->nullable();
            $table->string('theme_gray_100', 7)->nullable();
            $table->string('home_hero_eyebrow')->nullable();
            $table->string('home_hero_title')->nullable();
            $table->text('home_hero_description')->nullable();
            $table->string('home_hero_cta_label', 100)->nullable();
            $table->string('home_hero_cta_url', 2048)->nullable();
            $table->string('home_hero_cta_target', 10)->nullable();
            $table->string('home_hero_image_path')->nullable();
            $table->json('allies')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 80)->nullable();
            $table->string('contact_address')->nullable();
            $table->text('contact_hours')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->longText('symbols_content')->nullable();
            $table->string('symbols_shield_image_path')->nullable();
            $table->foreignId('contracting_manual_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * @param  array<string, mixed>  $legacyRow
     * @return array<string, mixed>
     */
    private function buildCurrentRowPayload(array $legacyRow): array
    {
        $now = now();

        return [
            'singleton' => (int) ($this->pick($legacyRow, ['singleton'], 1)) ?: 1,
            'institution_name' => (string) $this->pick(
                $legacyRow,
                ['institution_name', 'site_name'],
                'IED Agropecuaria Jose Maria Herrera',
            ),
            'dane' => $this->pick($legacyRow, ['dane', 'dane_code', 'institution_dane_code']),
            'nit' => $this->pick($legacyRow, ['nit', 'institution_nit']),
            'location' => $this->pick($legacyRow, ['location']),
            'academic_modality_label' => $this->pick($legacyRow, ['academic_modality_label']),
            'academic_modality_icon' => $this->pick($legacyRow, ['academic_modality_icon']),
            'rector_name' => $this->pick($legacyRow, ['rector_name']),
            'email' => $this->pick($legacyRow, ['email', 'contact_email']),
            'phone' => $this->pick($legacyRow, ['phone', 'contact_phone']),
            'address' => $this->pick($legacyRow, ['address', 'contact_address']),
            'siee' => $this->pick($legacyRow, ['siee']),
            'siee_name' => $this->pick($legacyRow, ['siee_name']),
            'siee_document_id' => $this->pick($legacyRow, ['siee_document_id']),
            'aula_virtual' => $this->pick($legacyRow, ['aula_virtual']),
            'aula_virtual_name' => $this->pick($legacyRow, ['aula_virtual_name']),
            'logo_path' => $this->pick($legacyRow, ['logo_path']),
            'theme_primary' => $this->pick($legacyRow, ['theme_primary']),
            'theme_primary_dark' => $this->pick($legacyRow, ['theme_primary_dark']),
            'theme_primary_light' => $this->pick($legacyRow, ['theme_primary_light']),
            'theme_accent' => $this->pick($legacyRow, ['theme_accent']),
            'theme_gray_900' => $this->pick($legacyRow, ['theme_gray_900']),
            'theme_gray_700' => $this->pick($legacyRow, ['theme_gray_700']),
            'theme_gray_600' => $this->pick($legacyRow, ['theme_gray_600']),
            'theme_gray_200' => $this->pick($legacyRow, ['theme_gray_200']),
            'theme_gray_100' => $this->pick($legacyRow, ['theme_gray_100']),
            'home_hero_eyebrow' => $this->pick($legacyRow, ['home_hero_eyebrow']),
            'home_hero_title' => $this->pick($legacyRow, ['home_hero_title']),
            'home_hero_description' => $this->pick($legacyRow, ['home_hero_description']),
            'home_hero_cta_label' => $this->pick($legacyRow, ['home_hero_cta_label']),
            'home_hero_cta_url' => $this->pick($legacyRow, ['home_hero_cta_url']),
            'home_hero_cta_target' => $this->pick($legacyRow, ['home_hero_cta_target']),
            'home_hero_image_path' => $this->pick($legacyRow, ['home_hero_image_path']),
            'allies' => $this->normalizeJsonValue($this->pick($legacyRow, ['allies'])),
            'contact_hours' => $this->pick($legacyRow, ['contact_hours']),
            'location_latitude' => $this->pick($legacyRow, ['location_latitude', 'location_lat']),
            'location_longitude' => $this->pick($legacyRow, ['location_longitude', 'location_lng']),
            'symbols_flag_intro' => $this->pick($legacyRow, ['symbols_flag_intro']),
            'symbols_flag_stripes' => $this->normalizeJsonValue($this->pick($legacyRow, ['symbols_flag_stripes'])),
            'symbols_shield_intro' => $this->pick($legacyRow, ['symbols_shield_intro']),
            'symbols_shield_image_path' => $this->pick($legacyRow, ['symbols_shield_image_path']),
            'symbols_shield_items' => $this->normalizeJsonValue($this->pick($legacyRow, ['symbols_shield_items'])),
            'symbols_hymn_title' => $this->pick($legacyRow, ['symbols_hymn_title']),
            'symbols_hymn_audio_path' => $this->pick($legacyRow, ['symbols_hymn_audio_path']),
            'symbols_hymn_lyrics' => $this->pick($legacyRow, ['symbols_hymn_lyrics', 'symbols_content']),
            'contracting_manual_document_id' => $this->pick($legacyRow, ['contracting_manual_document_id']),
            'created_at' => $this->pick($legacyRow, ['created_at'], $now),
            'updated_at' => $this->pick($legacyRow, ['updated_at'], $now),
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<int, string>  $keys
     */
    private function pick(array $source, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $source)) {
                continue;
            }

            $value = $source[$key];

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function normalizeJsonValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        $encoded = json_encode($value);

        return $encoded === false ? null : $encoded;
    }
};
