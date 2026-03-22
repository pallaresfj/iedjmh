<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('name');
            $table->string('nit')->nullable();
            $table->text('social_object')->nullable();
            $table->decimal('evaluation_score', 8, 2)->nullable();
            $table->boolean('is_awarded')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_participants');
    }
};
