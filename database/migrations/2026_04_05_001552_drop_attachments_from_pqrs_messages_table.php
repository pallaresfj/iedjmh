<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pqrs_messages', 'attachments')) {
            return;
        }

        Schema::table('pqrs_messages', function (Blueprint $table): void {
            $table->dropColumn('attachments');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pqrs_messages', 'attachments')) {
            return;
        }

        Schema::table('pqrs_messages', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('is_internal');
        });
    }
};
