<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class CategoryCatalogSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, description: string, sort_order: int, status: string}>
     */
    private const PARENT_CATEGORIES = [
        ['name' => 'Noticias', 'description' => 'Publicaciones informativas y comunicados de actualidad', 'sort_order' => 1, 'status' => 'published'],
        ['name' => 'Documentos', 'description' => 'Archivos institucionales, académicos, normativos y administrativos', 'sort_order' => 2, 'status' => 'published'],
        ['name' => 'Proyectos', 'description' => 'Iniciativas pedagógicas, institucionales, productivas y comunitarias', 'sort_order' => 3, 'status' => 'published'],
        ['name' => 'Eventos', 'description' => 'Actividades programadas institucionales, académicas, culturales y deportivas', 'sort_order' => 4, 'status' => 'published'],
        ['name' => 'Trámites y servicios', 'description' => 'Solicitudes, procedimientos y servicios al ciudadano', 'sort_order' => 5, 'status' => 'published'],
        ['name' => 'Preguntas frecuentes', 'description' => 'Respuestas organizadas a dudas recurrentes de la comunidad', 'sort_order' => 6, 'status' => 'published'],
        ['name' => 'Institucional', 'description' => 'Presentación oficial de quiénes somos como institución educativa.', 'sort_order' => 7, 'status' => 'published'],
        ['name' => 'Académico', 'description' => 'Información sobre la oferta educativa y los recursos de apoyo al aprendizaje.', 'sort_order' => 8, 'status' => 'published'],
        ['name' => 'Galería', 'description' => 'Memoria visual de la vida institucional.', 'sort_order' => 9, 'status' => 'published'],
    ];

    /**
     * @var array<int, array<parent: string, name: string, description: string, sort_order: int, status: string>>
     */
    private const SUBCATEGORIES = [
        ['parent' => 'Noticias', 'name' => 'Institucionales', 'description' => 'Comunicados, rectoría, anuncios generales', 'sort_order' => 1, 'status' => 'published'],
        ['parent' => 'Noticias', 'name' => 'Académicas', 'description' => 'Logros, evaluaciones, actividades de aula, ferias', 'sort_order' => 2, 'status' => 'published'],
        ['parent' => 'Noticias', 'name' => 'Convivencia y bienestar', 'description' => 'Salud escolar, orientación, inclusión, convivencia', 'sort_order' => 3, 'status' => 'published'],
        ['parent' => 'Noticias', 'name' => 'Cultura y deporte', 'description' => 'Eventos culturales, artísticos y deportivos', 'sort_order' => 4, 'status' => 'published'],
        ['parent' => 'Noticias', 'name' => 'Gobierno escolar y participación', 'description' => 'Elecciones, consejos, liderazgo estudiantil', 'sort_order' => 5, 'status' => 'published'],
        ['parent' => 'Noticias', 'name' => 'Comunidad y alianzas', 'description' => 'Convenios, visitas, articulaciones y proyección social', 'sort_order' => 6, 'status' => 'published'],
        ['parent' => 'Noticias', 'name' => 'Infraestructura y gestión', 'description' => 'Obras, dotación, tecnología, mejoras institucionales', 'sort_order' => 7, 'status' => 'published'],

        ['parent' => 'Documentos', 'name' => 'Institucionales', 'description' => 'PEI, misión, visión, manuales, organigrama', 'sort_order' => 1, 'status' => 'published'],
        ['parent' => 'Documentos', 'name' => 'Académicos', 'description' => 'Planes de estudio, horarios, calendario, sistemas de evaluación', 'sort_order' => 2, 'status' => 'published'],
        ['parent' => 'Documentos', 'name' => 'Normativos', 'description' => 'Resoluciones, acuerdos, circulares, actas', 'sort_order' => 3, 'status' => 'published'],
        ['parent' => 'Documentos', 'name' => 'Gestión y planeación', 'description' => 'PMI, plan de acción, informes de gestión', 'sort_order' => 4, 'status' => 'published'],
        ['parent' => 'Documentos', 'name' => 'Transparencia', 'description' => 'Presupuesto, contratación, rendición de cuentas', 'sort_order' => 5, 'status' => 'published'],
        ['parent' => 'Documentos', 'name' => 'Formatos y formularios', 'description' => 'Descargables para solicitudes y trámites', 'sort_order' => 6, 'status' => 'published'],

        ['parent' => 'Proyectos', 'name' => 'Académicos transversales', 'description' => 'Lectura, STEM, TIC, investigación, bilingüismo', 'sort_order' => 1, 'status' => 'published'],
        ['parent' => 'Proyectos', 'name' => 'Pedagógicos obligatorios', 'description' => 'Ambiental, democracia, sexualidad, riesgos, tiempo libre', 'sort_order' => 2, 'status' => 'published'],
        ['parent' => 'Proyectos', 'name' => 'Institucionales estratégicos', 'description' => 'Calidad, mejoramiento, permanencia, inclusión', 'sort_order' => 3, 'status' => 'published'],
        ['parent' => 'Proyectos', 'name' => 'Productivos o técnicos', 'description' => 'Agropecuario, agroindustrial, emprendimiento, granja escolar', 'sort_order' => 4, 'status' => 'published'],
        ['parent' => 'Proyectos', 'name' => 'Convivencia y comunidad', 'description' => 'Escuela de familias, liderazgo, servicio social', 'sort_order' => 5, 'status' => 'published'],
        ['parent' => 'Proyectos', 'name' => 'Alianzas externas', 'description' => 'SENA, universidades, alcaldía, empresas, ONG', 'sort_order' => 6, 'status' => 'published'],

        ['parent' => 'Eventos', 'name' => 'Académicos', 'description' => 'Simulacros, entregas, foros, ferias, socializaciones', 'sort_order' => 1, 'status' => 'published'],
        ['parent' => 'Eventos', 'name' => 'Institucionales', 'description' => 'Reuniones, jornadas pedagógicas, aniversarios, actos', 'sort_order' => 2, 'status' => 'published'],
        ['parent' => 'Eventos', 'name' => 'Participación', 'description' => 'Gobierno escolar, audiencias, encuentros comunitarios', 'sort_order' => 3, 'status' => 'published'],
        ['parent' => 'Eventos', 'name' => 'Culturales y deportivos', 'description' => 'Torneos, danzas, música, teatro, celebraciones', 'sort_order' => 4, 'status' => 'published'],
        ['parent' => 'Eventos', 'name' => 'Bienestar y orientación', 'description' => 'Escuela de padres, prevención, salud, convivencia', 'sort_order' => 5, 'status' => 'published'],

        ['parent' => 'Trámites y servicios', 'name' => 'Académicos', 'description' => 'Certificados, constancias, boletines, validaciones', 'sort_order' => 1, 'status' => 'published'],
        ['parent' => 'Trámites y servicios', 'name' => 'Admisiones y matrícula', 'description' => 'Inscripción, matrícula, renovación, traslados, retiros', 'sort_order' => 2, 'status' => 'published'],
        ['parent' => 'Trámites y servicios', 'name' => 'Atención a familias', 'description' => 'PQRSDF, actualización de datos, permisos, autorizaciones', 'sort_order' => 3, 'status' => 'published'],
        ['parent' => 'Trámites y servicios', 'name' => 'Egresados', 'description' => 'Certificados, actas, diplomas, consultas históricas', 'sort_order' => 4, 'status' => 'published'],
        ['parent' => 'Trámites y servicios', 'name' => 'Docentes y administrativos', 'description' => 'Certificaciones, solicitudes internas, permisos', 'sort_order' => 5, 'status' => 'published'],

        ['parent' => 'Preguntas frecuentes', 'name' => 'Matrícula', 'description' => 'Requisitos, fechas, documentos, cupos', 'sort_order' => 1, 'status' => 'published'],
        ['parent' => 'Preguntas frecuentes', 'name' => 'Vida académica', 'description' => 'Horarios, evaluación, recuperación, tareas', 'sort_order' => 2, 'status' => 'published'],
        ['parent' => 'Preguntas frecuentes', 'name' => 'Convivencia escolar', 'description' => 'Manual, faltas, rutas, atención de casos', 'sort_order' => 3, 'status' => 'published'],
        ['parent' => 'Preguntas frecuentes', 'name' => 'Servicios escolares', 'description' => 'PAE, transporte, biblioteca, orientación', 'sort_order' => 4, 'status' => 'published'],
        ['parent' => 'Preguntas frecuentes', 'name' => 'Trámites y certificados', 'description' => 'Solicitudes, tiempos, requisitos, entrega', 'sort_order' => 5, 'status' => 'published'],
        ['parent' => 'Preguntas frecuentes', 'name' => 'Participación y gobierno escolar', 'description' => 'Representantes, consejos, elecciones', 'sort_order' => 6, 'status' => 'published'],
    ];

    public function run(): void
    {
        $catalogSlugs = [];
        $parentCategoryBySlug = [];

        foreach (self::PARENT_CATEGORIES as $parentCategoryData) {
            $parentSlug = Str::slug($parentCategoryData['name']);

            $parentCategory = $this->upsertCategory(
                slug: $parentSlug,
                name: $parentCategoryData['name'],
                description: $parentCategoryData['description'],
                sortOrder: $parentCategoryData['sort_order'],
                status: $parentCategoryData['status'],
                parentId: null,
            );

            $parentCategoryBySlug[$parentSlug] = $parentCategory;
            $catalogSlugs[] = $parentSlug;
        }

        foreach (self::SUBCATEGORIES as $subcategoryData) {
            $parentSlug = Str::slug($subcategoryData['parent']);
            $parentCategory = $parentCategoryBySlug[$parentSlug] ?? null;

            if (! $parentCategory instanceof Category) {
                throw new RuntimeException(sprintf('Parent category "%s" is not configured in the catalog seeder.', $subcategoryData['parent']));
            }

            $subcategorySlug = sprintf('%s-%s', $parentSlug, Str::slug($subcategoryData['name']));

            $this->upsertCategory(
                slug: $subcategorySlug,
                name: $subcategoryData['name'],
                description: $subcategoryData['description'],
                sortOrder: $subcategoryData['sort_order'],
                status: $subcategoryData['status'],
                parentId: $parentCategory->id,
            );

            $catalogSlugs[] = $subcategorySlug;
        }

        Category::query()
            ->withTrashed()
            ->whereNotIn('slug', $catalogSlugs)
            ->get()
            ->each(fn (Category $category): bool => (bool) $category->forceDelete());
    }

    private function upsertCategory(
        string $slug,
        string $name,
        ?string $description,
        int $sortOrder,
        string $status,
        ?int $parentId,
    ): Category {
        $category = Category::query()->withTrashed()->where('slug', $slug)->first();

        if (! $category instanceof Category) {
            $category = new Category;
            $category->slug = $slug;
        }

        $category->name = $name;
        $category->description = $description;
        $category->status = $status;
        $category->sort_order = $sortOrder;
        $category->parent_id = $parentId;
        $category->save();

        if ($category->trashed()) {
            $category->restore();
        }

        return $category->refresh();
    }
}
