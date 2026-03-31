<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduates', function (Blueprint $table) {
            $table->id();
            $table->string('national_id', 80)->unique();
            $table->string('full_name');
            $table->unsignedSmallInteger('graduation_year')->index();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 80)->nullable();
            $table->string('current_occupation')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->timestamp('data_processing_consent_at')->nullable();
            $table->string('status', 20)->default('preloaded')->index();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('academic_title')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('graduation_act_number', 120)->nullable();
            $table->string('graduation_folio', 120)->nullable();
            $table->string('record_verification_status', 20)->default('pending')->index();
            $table->timestamps();

            $table->index('full_name');
        });

        Schema::create('graduate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_id')->constrained('graduates')->cascadeOnDelete();
            $table->string('title');
            $table->string('type_label')->nullable();
            $table->text('description')->nullable();
            $table->string('drive_url', 2048)->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_disk', 40)->default('local');
            $table->boolean('is_official')->default(false);
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['graduate_id', 'sort_order']);
        });

        Schema::create('graduate_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduate_password_reset_tokens');
        Schema::dropIfExists('graduate_documents');
        Schema::dropIfExists('graduates');
    }
};
