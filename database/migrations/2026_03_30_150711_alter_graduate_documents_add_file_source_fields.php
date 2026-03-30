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
        Schema::table('graduate_documents', function (Blueprint $table) {
            $table->string('drive_url', 2048)->nullable()->change();
            $table->string('file_path')->nullable()->after('drive_url');
            $table->string('file_disk', 40)->default('local')->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduate_documents', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_disk']);
            $table->string('drive_url', 2048)->nullable(false)->change();
        });
    }
};
