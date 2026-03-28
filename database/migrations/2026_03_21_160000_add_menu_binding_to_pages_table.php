<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('menu_binding')->nullable()->after('slug');
        });

        $slugToBindingMap = [
            'institucion-historia' => 'institucion.historia',
            'institucion-mision-vision' => 'institucion.mision-vision',
            'institucion-simbolos' => 'institucion.simbolos',
            'institucion-equipo-institucional' => 'institucion.equipo-institucional',
            'academico-niveles-educativos' => 'academico.niveles-educativos',
            'academico-modalidad-agropecuaria' => 'academico.modalidad-agropecuaria',
            'academico-planes-area' => 'academico.planes-area',
            'academico-sistema-evaluacion' => 'academico.sistema-evaluacion',
        ];

        foreach ($slugToBindingMap as $slug => $binding) {
            DB::table('pages')
                ->where('slug', $slug)
                ->whereNull('menu_binding')
                ->update([
                    'menu_binding' => $binding,
                    'updated_at' => now(),
                ]);
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->unique('menu_binding');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['menu_binding']);
            $table->dropColumn('menu_binding');
        });
    }
};
