<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_participants', function (Blueprint $table): void {
            $table->foreignId('contractor_id')
                ->nullable()
                ->after('contract_id')
                ->constrained('contractors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contract_participants', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('contractor_id');
        });
    }
};
