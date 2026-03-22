<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table): void {
            $table->id();
            $table->string('process_code')->unique();
            $table->unsignedSmallInteger('fiscal_year')->index();
            $table->foreignId('contract_type_id')->constrained('contract_types')->restrictOnDelete();
            $table->text('object');
            $table->decimal('official_budget', 15, 2)->nullable();
            $table->string('process_status')->default('en_curso')->index();
            $table->date('publication_date')->nullable()->index();
            $table->date('offers_deadline_date')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->date('award_date')->nullable()->index();
            $table->string('contractor_name')->nullable();
            $table->string('contractor_nit')->nullable();
            $table->text('contractor_social_object')->nullable();
            $table->string('secop_ii_url')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fiscal_year', 'process_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
