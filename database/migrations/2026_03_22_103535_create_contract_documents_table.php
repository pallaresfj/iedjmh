<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('stage')->default('soporte')->index();
            $table->string('document_type')->default('otro')->index();
            $table->string('title')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_disk')->default('public');
            $table->string('external_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_documents');
    }
};
