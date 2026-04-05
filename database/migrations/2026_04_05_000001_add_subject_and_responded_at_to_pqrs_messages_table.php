<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrs_messages', function (Blueprint $table): void {
            $table->string('subject')->nullable()->after('author_email');
            $table->timestamp('responded_at')->nullable()->after('message')->index();
        });

        DB::table('pqrs_messages')
            ->whereNull('responded_at')
            ->update([
                'responded_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('pqrs_messages', function (Blueprint $table): void {
            $table->dropIndex(['responded_at']);
            $table->dropColumn(['subject', 'responded_at']);
        });
    }
};
