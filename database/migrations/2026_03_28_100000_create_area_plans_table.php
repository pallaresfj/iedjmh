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
        Schema::create('area_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('area_name');
            $table->text('responsible_teachers');
            $table->string('icon')->default('menu_book');
            $table->string('plan_url', 2048);
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_plans');
    }
};
