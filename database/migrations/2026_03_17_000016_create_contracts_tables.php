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
            $table->text('description')->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_type_id')->constrained('contract_types')->restrictOnDelete();
            $table->string('process_code')->unique();
            $table->unsignedSmallInteger('fiscal_year')->index();
            $table->text('object');
            $table->decimal('official_budget', 15, 2)->nullable();
            $table->string('process_status', 40)->default('en_curso')->index();
            $table->date('publication_date')->nullable()->index();
            $table->date('offers_deadline_date')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->date('award_date')->nullable();
            $table->string('contractor_name')->nullable();
            $table->string('contractor_nit', 100)->nullable()->index();
            $table->text('contractor_social_object')->nullable();
            $table->string('secop_ii_url', 2048)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('stage', 40)->default('soporte')->index();
            $table->string('document_type')->index();
            $table->string('title');
            $table->string('external_url', 2048)->nullable();
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
