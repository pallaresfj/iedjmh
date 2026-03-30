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
        Schema::create('graduate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_id')->constrained('graduates')->cascadeOnDelete();
            $table->string('title');
            $table->string('type_label')->nullable();
            $table->text('description')->nullable();
            $table->string('drive_url', 2048);
            $table->boolean('is_official')->default(false);
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['graduate_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduate_documents');
    }
};
