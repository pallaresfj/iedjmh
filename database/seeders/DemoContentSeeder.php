<?php

namespace Database\Seeders;

use App\Models\AreaPlan;
use App\Models\Banner;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Contractor;
use App\Models\ContractParticipant;
use App\Models\ContractType;
use App\Models\Document;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\PqrsRequest;
use App\Models\Procedure;
use App\Models\Project;
use App\Models\Setting;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@iedagropivijay.edu.co')->first()
            ?? User::factory()->create(['email' => 'admin@iedagropivijay.edu.co', 'is_admin' => true]);

        $this->seedSettings();
        $this->seedCategories($admin);
        $this->seedPages($admin);
        $this->seedBanners($admin);
        $this->seedCampuses($admin);
        $this->seedStaffMembers($admin);
        $this->seedPosts($admin);
        $this->seedEvents($admin);
        $this->seedProjects($admin);
        $this->seedAreaPlans();
        $this->seedDocuments($admin);
        $this->seedFaqs($admin);
        $this->seedProcedures($admin);
        $this->seedContracts($admin);
        $this->seedPqrs();
    }

    private function seedSettings(): void
    {
        if (Setting::query()->exists()) {
            return;
        }

        Setting::query()->create([
            'singleton' => 1,
            'institution_name' => 'IED Agropecuaria Jose Maria Herrera',
            'institution_nit' => '819001234-5',
            'institution_dane_code' => '147551000123',
            'rector_name' => 'Francisco Pallares',
        ]);
    }

    private function seedCategories(User $admin): void
    {
        $names = [
            'Noticias Institucionales', 'Eventos Academicos', 'Documentos Legales',
            'Proyectos Pedagogicos', 'Tramites Academicos', 'Tramites Administrativos',
            'General',
        ];

        foreach ($names as $i => $name) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'status' => 'published',
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedPages(User $admin): void
    {
        $pages = [
            ['slug' => 'historia', 'title' => 'Historia', 'menu_binding' => 'institucion.historia'],
            ['slug' => 'mision-vision', 'title' => 'Mision y Vision', 'menu_binding' => 'institucion.mision-vision'],
            ['slug' => 'simbolos', 'title' => 'Simbolos Institucionales', 'menu_binding' => 'institucion.simbolos'],
            ['slug' => 'pei', 'title' => 'Proyecto Educativo Institucional', 'menu_binding' => 'institucion.pei'],
            ['slug' => 'manual-convivencia', 'title' => 'Manual de Convivencia', 'menu_binding' => 'institucion.manual-convivencia'],
            ['slug' => 'niveles-educativos', 'title' => 'Niveles Educativos', 'menu_binding' => 'academico.niveles-educativos'],
            ['slug' => 'modalidad-agropecuaria', 'title' => 'Modalidad Agropecuaria', 'menu_binding' => 'academico.modalidad-agropecuaria'],
            ['slug' => 'academico-planes-area', 'title' => 'Planes de Area', 'menu_binding' => 'academico.planes-area'],
            ['slug' => 'calendario-academico', 'title' => 'Calendario Academico', 'menu_binding' => 'academico.calendario-academico'],
        ];

        foreach ($pages as $page) {
            Page::query()->firstOrCreate(
                ['slug' => $page['slug']],
                [
                    ...$page,
                    'summary' => 'Contenido de ejemplo para la pagina '.$page['title'].'.',
                    'content' => '<p>Esta es una pagina de demostración. Edita este contenido desde el panel de administracion.</p>',
                    'status' => 'published',
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedBanners(User $admin): void
    {
        Banner::query()->firstOrCreate(
            ['slug' => 'hero-principal'],
            [
                'title' => 'Bienvenidos a la IED Agropecuaria Jose Maria Herrera',
                'slug' => 'hero-principal',
                'subtitle' => 'Formando lideres del campo con valores y conocimiento',
                'description' => 'Institucion educativa comprometida con la excelencia academica y el desarrollo rural.',
                'cta_label' => 'Conoce nuestra historia',
                'cta_url' => '/institucion/historia',
                'status' => 'published',
                'created_by' => $admin->id,
            ],
        );
    }

    private function seedCampuses(User $admin): void
    {
        $campuses = [
            ['name' => 'Sede Principal', 'address' => 'Calle Principal, Pivijay, Magdalena'],
            ['name' => 'Sede Bachillerato Rural', 'address' => 'Vereda El Bongo, Pivijay, Magdalena'],
        ];

        foreach ($campuses as $campus) {
            Campus::query()->firstOrCreate(
                ['slug' => Str::slug($campus['name'])],
                [
                    ...$campus,
                    'slug' => Str::slug($campus['name']),
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => 0,
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedStaffMembers(User $admin): void
    {
        $campus = Campus::query()->first();
        if (! $campus) {
            return;
        }

        $members = [
            ['full_name' => 'Francisco Pallares', 'position_title' => 'Rector', 'staff_group' => 'directive'],
            ['full_name' => 'Maria Rodriguez', 'position_title' => 'Coordinadora Academica', 'staff_group' => 'directive'],
            ['full_name' => 'Juan Perez', 'position_title' => 'Docente Ciencias Naturales', 'staff_group' => 'teacher'],
            ['full_name' => 'Ana Martinez', 'position_title' => 'Docente Matematicas', 'staff_group' => 'teacher'],
            ['full_name' => 'Carlos Gomez', 'position_title' => 'Secretario', 'staff_group' => 'administrative'],
        ];

        foreach ($members as $i => $member) {
            StaffMember::query()->firstOrCreate(
                ['full_name' => $member['full_name'], 'campus_id' => $campus->id],
                [
                    ...$member,
                    'campus_id' => $campus->id,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedPosts(User $admin): void
    {
        $category = Category::query()->where('slug', 'noticias-institucionales')->first();

        $posts = [
            ['title' => 'Inicio del ano escolar 2026', 'excerpt' => 'Damos la bienvenida a toda la comunidad educativa al nuevo ano escolar.'],
            ['title' => 'Jornada de integracion comunitaria', 'excerpt' => 'Exito total en nuestra jornada de integracion con padres de familia y comunidad.'],
            ['title' => 'Resultados Pruebas Saber 2025', 'excerpt' => 'Nuestros estudiantes obtuvieron resultados destacados en las pruebas nacionales.'],
        ];

        foreach ($posts as $i => $post) {
            $created = Post::query()->firstOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    ...$post,
                    'slug' => Str::slug($post['title']),
                    'content' => '<p>Contenido de ejemplo para la noticia. Edita desde el panel de administracion.</p>',
                    'status' => 'published',
                    'published_at' => now()->subDays($i * 3),
                    'is_featured' => $i === 0,
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );

            if ($category) {
                $created->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }

    private function seedEvents(User $admin): void
    {
        $category = Category::query()->where('slug', 'eventos-academicos')->first();

        $events = [
            ['title' => 'Dia del Campesino', 'summary' => 'Celebracion institucional del Dia del Campesino con actividades culturales.', 'days' => 15],
            ['title' => 'Feria Agropecuaria Escolar', 'summary' => 'Exposicion de proyectos productivos de los estudiantes.', 'days' => 30],
            ['title' => 'Reunion de Padres de Familia', 'summary' => 'Entrega de boletines del primer periodo academico.', 'days' => 7],
        ];

        foreach ($events as $i => $event) {
            $created = Event::query()->firstOrCreate(
                ['slug' => Str::slug($event['title'])],
                [
                    'title' => $event['title'],
                    'slug' => Str::slug($event['title']),
                    'summary' => $event['summary'],
                    'description' => '<p>Detalles del evento. Actualiza esta informacion desde el panel de administracion.</p>',
                    'location' => 'IED Agropecuaria Jose Maria Herrera, Pivijay',
                    'starts_at' => now()->addDays($event['days']),
                    'ends_at' => now()->addDays($event['days'])->addHours(4),
                    'is_all_day' => false,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );

            if ($category) {
                $created->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }

    private function seedProjects(User $admin): void
    {
        $category = Category::query()->where('slug', 'proyectos-pedagogicos')->first();

        $projects = [
            ['title' => 'Huerta Escolar Sostenible', 'summary' => 'Proyecto de produccion agricola limpia liderado por estudiantes.'],
            ['title' => 'Cria de Especies Menores', 'summary' => 'Formacion practica en produccion pecuaria con enfoque ambiental.'],
        ];

        foreach ($projects as $i => $project) {
            $created = Project::query()->firstOrCreate(
                ['slug' => Str::slug($project['title'])],
                [
                    ...$project,
                    'slug' => Str::slug($project['title']),
                    'description' => '<p>Descripcion del proyecto. Actualiza desde el panel de administracion.</p>',
                    'status' => 'published',
                    'published_at' => now(),
                    'is_featured' => $i === 0,
                    'sort_order' => $i,
                    'gallery_image_paths' => [],
                    'created_by' => $admin->id,
                ],
            );

            if ($category) {
                $created->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }

    private function seedAreaPlans(): void
    {
        $plans = [
            [
                'area_name' => 'Matematicas',
                'responsible_teachers' => 'Claudia Perez, Ricardo Mendoza, Sofia Castro',
                'icon' => 'calculate',
                'plan_url' => 'https://example.com/planes/matematicas',
            ],
            [
                'area_name' => 'Ciencias Naturales',
                'responsible_teachers' => 'Luis Gomez, Marina Silva',
                'icon' => 'science',
                'plan_url' => 'https://example.com/planes/ciencias-naturales',
            ],
            [
                'area_name' => 'Tecnica Agropecuaria',
                'responsible_teachers' => 'Carlos Ruiz, Patricia Jaramillo',
                'icon' => 'agriculture',
                'plan_url' => 'https://example.com/planes/tecnica-agropecuaria',
            ],
            [
                'area_name' => 'Humanidades e Ingles',
                'responsible_teachers' => 'Elena White, Jorge Isaacs',
                'icon' => 'language',
                'plan_url' => 'https://example.com/planes/humanidades-ingles',
            ],
            [
                'area_name' => 'Ciencias Sociales',
                'responsible_teachers' => 'Mateo Holguin, Lucia Mendez',
                'icon' => 'history_edu',
                'plan_url' => 'https://example.com/planes/ciencias-sociales',
            ],
        ];

        foreach ($plans as $index => $plan) {
            AreaPlan::query()->firstOrCreate(
                ['area_name' => $plan['area_name']],
                [
                    ...$plan,
                    'status' => 'published',
                    'sort_order' => $index,
                    'published_at' => now(),
                ],
            );
        }
    }

    private function seedDocuments(User $admin): void
    {
        $category = Category::query()->where('slug', 'documentos-legales')->first();

        $docs = [
            ['title' => 'Manual de Convivencia 2026', 'document_number' => 'DOC-0001'],
            ['title' => 'Plan de Mejoramiento Institucional', 'document_number' => 'DOC-0002'],
            ['title' => 'Informe de Gestion 2025', 'document_number' => 'DOC-0003'],
        ];

        foreach ($docs as $i => $doc) {
            $created = Document::query()->firstOrCreate(
                ['slug' => Str::slug($doc['title'])],
                [
                    ...$doc,
                    'slug' => Str::slug($doc['title']),
                    'summary' => 'Documento institucional disponible para consulta publica.',
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );

            if ($category) {
                $created->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }

    private function seedFaqs(User $admin): void
    {
        $category = Category::query()->where('slug', 'general')->first();

        $faqs = [
            ['question' => 'Como solicitar un certificado de estudios?', 'answer' => 'Debe acercarse a secretaria con fotocopia del documento de identidad y realizar la solicitud por escrito. El tiempo de respuesta es de 5 dias habiles.'],
            ['question' => 'Cuales son los horarios de atencion?', 'answer' => 'La atencion al publico es de lunes a viernes de 7:00 AM a 12:00 PM y de 2:00 PM a 5:00 PM.'],
            ['question' => 'Como matricular a mi hijo?', 'answer' => 'El proceso de matricula se realiza en las fechas establecidas por la Secretaria de Educacion. Debe presentar los documentos requeridos en la sede principal.'],
            ['question' => 'Donde puedo consultar el calendario academico?', 'answer' => 'El calendario academico esta disponible en la seccion Academico > Calendario Academico de este sitio web.'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::query()->firstOrCreate(
                ['slug' => Str::slug(Str::limit($faq['question'], 80, ''))],
                [
                    ...$faq,
                    'slug' => Str::slug(Str::limit($faq['question'], 80, '')),
                    'category_id' => $category?->id,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedProcedures(User $admin): void
    {
        $catAcad = Category::query()->where('slug', 'tramites-academicos')->first();
        $catAdmin = Category::query()->where('slug', 'tramites-administrativos')->first();

        $procedures = [
            ['name' => 'Certificado de Estudios', 'summary' => 'Expedicion de certificados de calificaciones y constancias de estudio.', 'response_time' => '5 dias habiles', 'cost' => 'Gratuito', 'channel' => 'Presencial', 'category_id' => $catAcad?->id],
            ['name' => 'Matricula Nuevos Estudiantes', 'summary' => 'Proceso de inscripcion y matricula para estudiantes nuevos.', 'response_time' => 'Inmediato', 'cost' => 'Gratuito', 'channel' => 'Presencial', 'category_id' => $catAcad?->id],
            ['name' => 'Solicitud de Permiso Especial', 'summary' => 'Tramite para permisos de ausencia justificada.', 'response_time' => '2 dias habiles', 'cost' => 'Gratuito', 'channel' => 'Presencial', 'category_id' => $catAdmin?->id],
        ];

        foreach ($procedures as $i => $proc) {
            Procedure::query()->firstOrCreate(
                ['slug' => Str::slug($proc['name'])],
                [
                    ...$proc,
                    'slug' => Str::slug($proc['name']),
                    'requirements' => 'Fotocopia del documento de identidad. Solicitud por escrito.',
                    'is_online' => false,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $i,
                    'created_by' => $admin->id,
                ],
            );
        }
    }

    private function seedContracts(User $admin): void
    {
        $type = ContractType::query()->firstOrCreate(
            ['slug' => 'suministros'],
            [
                'name' => 'Suministros',
                'slug' => 'suministros',
                'status' => 'published',
                'sort_order' => 0,
                'created_by' => $admin->id,
            ],
        );

        $contractor = Contractor::query()->firstOrCreate(
            ['nit' => '900123456-1'],
            [
                'name' => 'Suministros del Caribe SAS',
                'nit' => '900123456-1',
                'social_object' => 'Suministro de insumos y materiales educativos.',
                'is_active' => true,
            ],
        );

        $contract = Contract::query()->firstOrCreate(
            ['process_code' => 'FSE-001-2026'],
            [
                'process_code' => 'FSE-001-2026',
                'fiscal_year' => 2026,
                'contract_type_id' => $type->id,
                'object' => 'Suministro de material didactico para laboratorios agropecuarios.',
                'official_budget' => 15000000,
                'process_status' => 'en_curso',
                'publication_date' => now()->subDays(5),
                'offers_deadline_date' => now()->addDays(10),
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $admin->id,
            ],
        );

        ContractDocument::query()->firstOrCreate(
            ['contract_id' => $contract->id, 'document_type' => 'estudios_previos'],
            [
                'contract_id' => $contract->id,
                'stage' => 'convocatoria',
                'document_type' => 'estudios_previos',
                'title' => 'Estudios Previos - Suministro Material Didactico',
                'external_url' => '#',
                'sort_order' => 0,
            ],
        );

        ContractParticipant::query()->firstOrCreate(
            ['contract_id' => $contract->id, 'contractor_id' => $contractor->id],
            [
                'contract_id' => $contract->id,
                'contractor_id' => $contractor->id,
                'name' => $contractor->name,
                'nit' => $contractor->nit,
                'social_object' => $contractor->social_object,
                'is_awarded' => false,
                'sort_order' => 0,
            ],
        );
    }

    private function seedPqrs(): void
    {
        PqrsRequest::query()->firstOrCreate(
            ['tracking_code' => 'PQRS-DEMO-0001'],
            [
                'tracking_code' => 'PQRS-DEMO-0001',
                'type' => 'peticion',
                'status' => 'received',
                'priority' => 'medium',
                'subject' => 'Solicitud de informacion sobre modalidad agropecuaria',
                'message' => 'Buenos dias, quisiera conocer los requisitos para inscribir a mi hijo en la modalidad agropecuaria del proximo año. Agradezco su orientacion.',
                'applicant_name' => 'Pedro Garcia',
                'applicant_email' => 'pedro.garcia@example.com',
                'applicant_phone' => '3001234567',
                'applicant_document' => '12345678',
                'municipality' => 'Pivijay',
                'consent_habeas_data' => true,
                'submitted_at' => now()->subDays(2),
            ],
        );
    }
}
