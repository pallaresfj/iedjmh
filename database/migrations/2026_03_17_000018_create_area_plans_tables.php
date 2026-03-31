<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_plans', function (Blueprint $table) {
            $table->id();
            $table->string('area_name');
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('intensity', 50)->nullable();
            $table->longText('general_objective')->nullable();
            $table->json('specific_objectives')->nullable();
            $table->json('grade_topics')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('area_plan_staff_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_plan_id')->constrained('area_plans')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['area_plan_id', 'staff_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_plan_staff_member');
        Schema::dropIfExists('area_plans');
    }
};
