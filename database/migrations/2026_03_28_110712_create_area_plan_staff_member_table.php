<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_plan_staff_member', function (Blueprint $table): void {
            $table->foreignId('area_plan_id')->constrained('area_plans')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['area_plan_id', 'staff_member_id']);
            $table->index(['staff_member_id', 'area_plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_plan_staff_member');
    }
};
