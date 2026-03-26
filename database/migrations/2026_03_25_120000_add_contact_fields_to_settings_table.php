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
        'address',
        'phone',
        'email',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('settings') || $this->allColumnsExist()) {
            return;
        }

        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'address')) {
                $table->string('address')->nullable()->after('location');
            }

            if (! Schema::hasColumn('settings', 'phone')) {
                $table->string('phone', 80)->nullable()->after('address');
            }

            if (! Schema::hasColumn('settings', 'email')) {
                $table->string('email')->nullable()->after('phone');
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
