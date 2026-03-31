<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_type_id')->constrained('contract_types')->restrictOnDelete();
            $table->string('contract_number')->index();
            $table->unsignedSmallInteger('year')->index();
            $table->string('object');
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type')->index();
            $table->string('drive_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nit', 100)->unique();
            $table->text('social_object');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('contract_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();
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
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('contract_documents');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_types');
    }
};
