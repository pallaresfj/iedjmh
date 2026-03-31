<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matricula_requests', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('grade')->index();
            $table->string('document_number')->index();
            $table->string('phone', 80)->nullable();
            $table->foreignId('campus_id')->constrained('campuses')->restrictOnDelete();
            $table->json('attachments')->nullable();
            $table->string('status')->default('pending')->index();
            $table->longText('internal_notes')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matricula_requests');
    }
};
