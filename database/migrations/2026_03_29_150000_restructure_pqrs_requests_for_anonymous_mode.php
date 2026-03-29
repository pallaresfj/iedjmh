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
        Schema::table('pqrs_requests', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('type')->index();
        });

        Schema::table('pqrs_requests', function (Blueprint $table) {
            $table->string('applicant_name')->nullable()->change();
        });

        Schema::table('pqrs_requests', function (Blueprint $table) {
            $table->dropColumn(['subject', 'municipality']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pqrs_requests', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('priority');
            $table->string('municipality')->nullable()->after('applicant_address');
            $table->dropColumn('is_anonymous');
        });

        DB::table('pqrs_requests')
            ->whereNull('applicant_name')
            ->update(['applicant_name' => 'Anonimo']);

        Schema::table('pqrs_requests', function (Blueprint $table) {
            $table->string('applicant_name')->nullable(false)->change();
        });
    }
};
