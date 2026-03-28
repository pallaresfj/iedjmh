<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('pages')) {
            return;
        }

        $now = now();

        $existingByBinding = DB::table('pages')
            ->where('menu_binding', 'academico.planes-area')
            ->first();

        if ($existingByBinding) {
            return;
        }

        $existingBySlug = DB::table('pages')
            ->where('slug', 'academico-planes-area')
            ->first();

        if ($existingBySlug) {
            DB::table('pages')
                ->where('id', $existingBySlug->id)
                ->update([
                    'menu_binding' => 'academico.planes-area',
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('pages')->insert([
            'title' => 'Planes de Area',
            'slug' => 'academico-planes-area',
            'menu_binding' => 'academico.planes-area',
            'summary' => 'Consulta de planes curriculares, mallas y orientaciones por area.',
            'content' => null,
            'status' => 'published',
            'created_by' => null,
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // Migracion de backfill de datos: no reversible de forma segura.
    }
};
