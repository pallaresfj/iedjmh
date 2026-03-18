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
        Schema::create('pqrs_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('type')->index();
            $table->string('status')->default('received')->index();
            $table->string('priority')->default('medium')->index();
            $table->string('subject');
            $table->longText('message');
            $table->string('applicant_name');
            $table->string('applicant_email')->nullable()->index();
            $table->string('applicant_phone')->nullable();
            $table->string('applicant_document')->nullable();
            $table->string('applicant_address')->nullable();
            $table->string('municipality')->nullable();
            $table->boolean('consent_habeas_data')->default(false);
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('internal_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pqrs_requests');
    }
};
