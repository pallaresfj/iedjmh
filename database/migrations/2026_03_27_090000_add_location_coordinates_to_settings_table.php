<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $columns = [
        'location_latitude',
        'location_longitude',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings') || $this->allColumnsExist()) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'location_latitude')) {
                $table->decimal('location_latitude', 10, 7)->nullable()->after('location');
            }

            if (! Schema::hasColumn('settings', 'location_longitude')) {
                $table->decimal('location_longitude', 10, 7)->nullable()->after('location_latitude');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function allColumnsExist(): bool
    {
        foreach ($this->columns as $column) {
            if (! Schema::hasColumn('settings', $column)) {
                return false;
            }
        }

        return true;
    }
};
