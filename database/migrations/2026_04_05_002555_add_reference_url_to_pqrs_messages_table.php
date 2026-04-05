<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pqrs_messages', 'reference_url')) {
            return;
        }

        Schema::table('pqrs_messages', function (Blueprint $table): void {
            $table->string('reference_url', 2048)->nullable()->after('message');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pqrs_messages', 'reference_url')) {
            return;
        }

        Schema::table('pqrs_messages', function (Blueprint $table): void {
            $table->dropColumn('reference_url');
        });
    }
};
