<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('published')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        $defaults = [
            'Suministros',
            'Prestacion de Servicios',
            'Mantenimiento',
            'Obra',
            'Consultoria',
        ];

        $now = now();

        DB::table('contract_types')->insert(
            collect($defaults)
                ->map(fn (string $name, int $index): array => [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'status' => 'published',
                    'sort_order' => ($index + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all(),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_types');
    }
};
