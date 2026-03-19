<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('procedures')
            ->whereNull('summary')
            ->whereNotNull('description')
            ->update([
                'summary' => DB::raw('description'),
            ]);

        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->longText('description')->nullable()->after('summary');
        });

        DB::table('procedures')
            ->whereNull('description')
            ->whereNotNull('summary')
            ->update([
                'description' => DB::raw('summary'),
            ]);
    }
};
