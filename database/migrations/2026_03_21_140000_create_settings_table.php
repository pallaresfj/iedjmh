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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('institution_name')->nullable();
            $table->string('dane')->nullable();
            $table->string('nit')->nullable();
            $table->string('location')->nullable();
            $table->string('siee')->nullable();
            $table->string('aula_virtual')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedTinyInteger('singleton')->default(1)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
