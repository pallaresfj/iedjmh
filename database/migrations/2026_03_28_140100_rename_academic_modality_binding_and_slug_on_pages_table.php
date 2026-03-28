<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        if (Schema::hasColumn('pages', 'menu_binding')) {
            DB::table('pages')
                ->where('menu_binding', 'academico.modalidad-agropecuaria')
                ->update([
                    'menu_binding' => 'academico.modalidad',
                    'updated_at' => now(),
                ]);
        }

        if (! Schema::hasColumn('pages', 'slug')) {
            return;
        }

        $legacySlugs = [
            'academico-modalidad-agropecuaria',
            'modalidad-agropecuaria',
        ];

        $legacyRows = DB::table('pages')
            ->whereIn('slug', $legacySlugs)
            ->orderBy('id')
            ->get(['id']);

        foreach ($legacyRows as $row) {
            $targetTaken = DB::table('pages')
                ->where('slug', 'academico-modalidad')
                ->where('id', '!=', $row->id)
                ->exists();

            if ($targetTaken) {
                continue;
            }

            DB::table('pages')
                ->where('id', $row->id)
                ->update([
                    'slug' => 'academico-modalidad',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        if (Schema::hasColumn('pages', 'menu_binding')) {
            DB::table('pages')
                ->where('menu_binding', 'academico.modalidad')
                ->update([
                    'menu_binding' => 'academico.modalidad-agropecuaria',
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasColumn('pages', 'slug')) {
            $canRestoreLegacySlug = ! DB::table('pages')
                ->where('slug', 'academico-modalidad-agropecuaria')
                ->exists();

            if ($canRestoreLegacySlug) {
                DB::table('pages')
                    ->where('slug', 'academico-modalidad')
                    ->update([
                        'slug' => 'academico-modalidad-agropecuaria',
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};
