<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
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
            // Theme colors
            $table->string('theme_primary', 7)->nullable();
            $table->string('theme_primary_dark', 7)->nullable();
            $table->string('theme_primary_light', 7)->nullable();
            $table->string('theme_accent', 7)->nullable();
            $table->string('theme_gray_900', 7)->nullable();
            $table->string('theme_gray_700', 7)->nullable();
            $table->string('theme_gray_600', 7)->nullable();
            $table->string('theme_gray_200', 7)->nullable();
            $table->string('theme_gray_100', 7)->nullable();
            // Home hero
            $table->string('home_hero_eyebrow')->nullable();
            $table->string('home_hero_title')->nullable();
            $table->text('home_hero_description')->nullable();
            $table->string('home_hero_cta_label', 100)->nullable();
            $table->string('home_hero_cta_url', 2048)->nullable();
            $table->string('home_hero_cta_target', 10)->nullable();
            $table->string('home_hero_image_path')->nullable();
            // Allies
            $table->json('allies')->nullable();
            // Contact
            $table->text('contact_hours')->nullable();
            // Location coordinates
            $table->decimal('location_latitude', 10, 7)->nullable();
            $table->decimal('location_longitude', 10, 7)->nullable();
            // Symbols
            $table->text('symbols_flag_intro')->nullable();
            $table->json('symbols_flag_stripes')->nullable();
            $table->text('symbols_shield_intro')->nullable();
            $table->string('symbols_shield_image_path')->nullable();
            $table->json('symbols_shield_items')->nullable();
            $table->string('symbols_hymn_title', 160)->nullable();
            $table->string('symbols_hymn_audio_path')->nullable();
            $table->longText('symbols_hymn_lyrics')->nullable();
            // Contracting manual
            $table->foreignId('contracting_manual_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
