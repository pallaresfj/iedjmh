<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pqrs_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('type')->index();
            $table->boolean('is_anonymous')->default(false)->index();
            $table->string('priority')->default('normal')->index();
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->string('applicant_name')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->string('applicant_document_number')->nullable();
            $table->string('applicant_address')->nullable();
            $table->string('status')->default('open')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_requests');
    }
};
