<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('categories', 'status')) {
                $table->string('status', 20)->default('draft')->index();
            }

            if (! Schema::hasColumn('categories', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index();
            }

            if (! Schema::hasColumn('categories', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->index();
            }

            if (! Schema::hasColumn('categories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->index();
            }

            if (! Schema::hasColumn('categories', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->index();
            }

            if (! Schema::hasColumn('categories', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Legacy schema had a required `type` column. Make it optional to avoid
        // write failures from current model/forms/seeders, which do not use it.
        if (Schema::hasColumn('categories', 'type') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `categories` MODIFY `type` VARCHAR(255) NULL DEFAULT 'general'");
        }
    }

    public function down(): void
    {
        // Intentionally left as no-op to avoid destructive rollback in production.
    }
};
