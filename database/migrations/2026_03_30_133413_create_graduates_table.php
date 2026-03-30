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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduates');
    }
};
