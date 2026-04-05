<?php

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Event;
use App\Models\Post;
use App\Models\PqrsRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('admin dashboard renders agro emerald widgets', function () {
    $this->actingAs(adminDashboardUser());

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Moderacion de noticias')
        ->assertSee('Gestion de Noticias Recientes')
        ->assertSee('Ultimas Solicitudes PQRSF')
        ->assertSee('Estado de Contratacion')
        ->assertSee('Proximos Eventos');
});

test('admin dashboard shows expected kpi totals', function () {
    $this->actingAs(adminDashboardUser());

    $now = CarbonImmutable::now('America/Bogota');
    $weekStart = $now->startOfWeek();

    foreach (range(1, 12) as $index) {
        Post::query()->create([
            'title' => "Noticia publicada {$index}",
            'slug' => "noticia-publicada-{$index}",
            'status' => 'published',
            'published_at' => $now->subHours($index),
            'sort_order' => $index,
        ]);
    }

    Post::query()->create([
        'title' => 'Noticia borrador',
        'slug' => 'noticia-borrador',
        'status' => 'draft',
        'sort_order' => 999,
    ]);

    foreach (range(1, 7) as $index) {
        Event::query()->create([
            'title' => "Evento semanal {$index}",
            'slug' => "evento-semanal-{$index}",
            'status' => 'published',
            'starts_at' => $weekStart->addDays($index)->setTime(8, 0),
            'published_at' => $now->subDay(),
            'sort_order' => $index,
        ]);
    }

    Event::query()->create([
        'title' => 'Evento fuera de semana',
        'slug' => 'evento-fuera-semana',
        'status' => 'published',
        'starts_at' => $now->addWeeks(2),
        'published_at' => $now->subDay(),
        'sort_order' => 99,
    ]);

    foreach (range(1, 9) as $index) {
        PqrsRequest::query()->create([
            'tracking_code' => "PQRS-2026-PEN-{$index}",
            'type' => 'peticion',
            'is_anonymous' => false,
            'status' => 'received',
            'priority' => 'medium',
            'message' => str_repeat('Mensaje de prueba pendiente. ', 2),
            'applicant_name' => "Usuario {$index}",
            'applicant_email' => "usuario{$index}@example.test",
            'submitted_at' => $now->subHours($index),
        ]);
    }

    foreach (range(1, 3) as $index) {
        PqrsRequest::query()->create([
            'tracking_code' => "PQRS-2026-CER-{$index}",
            'type' => 'peticion',
            'is_anonymous' => false,
            'status' => 'closed',
            'priority' => 'medium',
            'message' => str_repeat('Mensaje de prueba cerrada. ', 2),
            'applicant_name' => "Cerrado {$index}",
            'applicant_email' => "cerrado{$index}@example.test",
            'submitted_at' => $now->subDays($index),
            'resolved_at' => $now->subHours($index),
        ]);
    }

    foreach (range(1, 6) as $index) {
        createDashboardContract([
            'process_status' => $index <= 4 ? 'en_curso' : 'adjudicado',
            'status' => 'published',
            'published_at' => $now->subDays($index),
        ]);
    }

    createDashboardContract([
        'process_status' => 'finalizado',
        'status' => 'published',
        'published_at' => $now->subDays(10),
    ]);

    $expectedPendingPqrs = PqrsRequest::query()
        ->where(function ($query): void {
            $query
                ->whereNull('resolved_at')
                ->orWhereNotIn('status', ['resolved', 'closed', 'resuelto', 'cerrado', 'finalizado']);
        })
        ->count();

    $expectedEventsThisWeek = Event::query()
        ->where('status', 'published')
        ->whereNotNull('starts_at')
        ->whereBetween('starts_at', [$now->startOfWeek(), $now->endOfWeek()])
        ->count();

    $expectedPublishedPosts = Post::query()
        ->where('status', 'published')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', $now)
        ->count();

    $expectedActiveContracts = Contract::query()
        ->where('status', 'published')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', $now)
        ->whereIn('process_status', ['en_curso', 'adjudicado'])
        ->count();

    $content = $this->get('/admin')->assertOk()->getContent();

    expect($content)
        ->toMatch('/agro-kpi-card__value">\s*'.preg_quote((string) $expectedPendingPqrs, '/').'\s*<\/p>\s*<p class="agro-kpi-card__label">PQRS pendientes/s')
        ->toMatch('/agro-kpi-card__value">\s*'.preg_quote((string) $expectedEventsThisWeek, '/').'\s*<\/p>\s*<p class="agro-kpi-card__label">Eventos esta semana/s')
        ->toMatch('/agro-kpi-card__value">\s*'.preg_quote((string) $expectedPublishedPosts, '/').'\s*<\/p>\s*<p class="agro-kpi-card__label">Noticias publicadas/s')
        ->toMatch('/agro-kpi-card__value">\s*'.preg_quote((string) $expectedActiveContracts, '/').'\s*<\/p>\s*<p class="agro-kpi-card__label">Contratos activos/s');
});

test('admin dashboard lists recent news pqrs and upcoming events', function () {
    $this->actingAs(adminDashboardUser());

    $author = User::factory()->create();

    Post::query()->create([
        'title' => 'Noticia Rectoral',
        'slug' => 'noticia-rectoral',
        'status' => 'published',
        'published_at' => now()->subHour(),
        'created_by' => $author->id,
        'updated_by' => $author->id,
        'sort_order' => 1,
    ]);

    PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-LISTA-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Solicitud de prueba. ', 2),
        'applicant_name' => 'Maria Cardenas',
        'applicant_email' => 'maria.cardenas@example.test',
        'submitted_at' => now()->subHours(2),
    ]);

    Event::query()->create([
        'title' => 'Feria de emprendimiento agropecuario',
        'slug' => 'feria-emprendimiento-agropecuario',
        'status' => 'published',
        'starts_at' => now()->addDays(3)->setTime(10, 0),
        'location' => 'Plaza central',
        'published_at' => now()->subDay(),
        'sort_order' => 1,
    ]);

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Noticia Rectoral')
        ->assertSee('Solicitud de prueba.')
        ->assertSee('Feria de emprendimiento agropecuario');
});

test('admin dashboard displays contracting progress summary', function () {
    $this->actingAs(adminDashboardUser());

    $year = (int) now('America/Bogota')->year;

    createDashboardContract([
        'fiscal_year' => $year,
        'process_status' => 'en_curso',
        'status' => 'published',
        'published_at' => now()->subDays(1),
    ]);

    createDashboardContract([
        'fiscal_year' => $year,
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now()->subDays(2),
    ]);

    createDashboardContract([
        'fiscal_year' => $year,
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now()->subDays(3),
    ]);

    createDashboardContract([
        'fiscal_year' => $year,
        'process_status' => 'finalizado',
        'status' => 'published',
        'published_at' => now()->subDays(4),
    ]);

    $this->get('/admin')
        ->assertOk()
        ->assertSee("Vigencia {$year}")
        ->assertSee('75%')
        ->assertSee('Total vigencia: 4');
});

test('dashboard disables resource links when user lacks permissions', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([
        Permission::findOrCreate('ViewAny:Post', 'web'),
        Permission::findOrCreate('View:Post', 'web'),
    ]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $post = Post::query()->create([
        'title' => 'Noticia con acceso parcial',
        'slug' => 'noticia-acceso-parcial',
        'status' => 'published',
        'published_at' => now()->subHour(),
        'sort_order' => 1,
    ]);

    Event::query()->create([
        'title' => 'Evento restringido',
        'slug' => 'evento-restringido',
        'status' => 'published',
        'starts_at' => now()->addDays(2),
        'published_at' => now()->subHour(),
        'sort_order' => 1,
    ]);

    createDashboardContract([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $pqrs = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-DENY-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Sin acceso. ', 4),
        'applicant_name' => 'Solicitante restringido',
        'applicant_email' => 'restringido@example.test',
        'submitted_at' => now()->subHour(),
    ]);

    $postsIndexUrl = route('filament.admin.resources.posts.index');
    $postsCreateUrl = route('filament.admin.resources.posts.create');
    $postEditUrl = route('filament.admin.resources.posts.edit', ['record' => $post]);
    $eventsIndexUrl = route('filament.admin.resources.events.index');
    $eventsCreateUrl = route('filament.admin.resources.events.create');
    $contractsIndexUrl = route('filament.admin.resources.contracts.index');
    $contractsCreateUrl = route('filament.admin.resources.contracts.create');
    $pqrsIndexUrl = route('filament.admin.resources.pqrs-requests.index');
    $pqrsEditUrl = route('filament.admin.resources.pqrs-requests.edit', ['record' => $pqrs]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee($postsIndexUrl, false)
        ->assertDontSee($postsCreateUrl, false)
        ->assertDontSee($postEditUrl, false)
        ->assertDontSee($eventsIndexUrl, false)
        ->assertDontSee($eventsCreateUrl, false)
        ->assertDontSee($contractsIndexUrl, false)
        ->assertDontSee($contractsCreateUrl, false)
        ->assertDontSee($pqrsIndexUrl, false)
        ->assertDontSee($pqrsEditUrl, false)
        ->assertDontSee('Ver calendario completo')
        ->assertDontSee('Ver procesos');
});

test('dashboard exposes pqrs links when user has pqrs permissions', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([
        Permission::findOrCreate('ViewAny:PqrsRequest', 'web'),
        Permission::findOrCreate('View:PqrsRequest', 'web'),
        Permission::findOrCreate('Update:PqrsRequest', 'web'),
    ]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $pqrs = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-LINK-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => str_repeat('Mensaje de prueba. ', 4),
        'applicant_name' => 'Persona con enlace',
        'applicant_email' => 'enlace@example.test',
        'submitted_at' => now()->subMinutes(30),
    ]);

    $pqrsIndexUrl = route('filament.admin.resources.pqrs-requests.index');
    $pqrsEditUrl = route('filament.admin.resources.pqrs-requests.edit', ['record' => $pqrs]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee($pqrsIndexUrl, false)
        ->assertSee($pqrsEditUrl, false)
        ->assertSee('Revisar');
});

test('dashboard shows post create shortcut when user can create posts', function () {
    $role = Role::findOrCreate('editor', 'web');
    $role->syncPermissions([
        Permission::findOrCreate('ViewAny:Post', 'web'),
        Permission::findOrCreate('View:Post', 'web'),
        Permission::findOrCreate('Create:Post', 'web'),
    ]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $postsCreateUrl = route('filament.admin.resources.posts.create');
    $postsIndexUrl = route('filament.admin.resources.posts.index');

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee($postsCreateUrl, false)
        ->assertSee('aria-label="Crear noticia"', false)
        ->assertSee($postsIndexUrl, false);
});

test('dashboard shows contract create shortcut when user can create contracts', function () {
    $role = Role::findOrCreate('administrador', 'web');
    $role->syncPermissions([
        Permission::findOrCreate('ViewAny:Contract', 'web'),
        Permission::findOrCreate('Create:Contract', 'web'),
    ]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $contractsCreateUrl = route('filament.admin.resources.contracts.create');

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee($contractsCreateUrl, false)
        ->assertSee('aria-label="Crear proceso contractual"', false);
});

test('dashboard hides edit link when resource route binding would return 404', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([
        Permission::findOrCreate('ViewAny:Post', 'web'),
        Permission::findOrCreate('View:Post', 'web'),
        Permission::findOrCreate('Update:Post', 'web'),
    ]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $publishedPost = Post::query()->create([
        'title' => 'Noticia publicada restringida',
        'slug' => 'noticia-publicada-restringida',
        'status' => 'published',
        'published_at' => now()->subHour(),
        'sort_order' => 1,
    ]);

    $editUrl = route('filament.admin.resources.posts.edit', ['record' => $publishedPost]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Noticia publicada restringida')
        ->assertDontSee($editUrl, false);
});

test('dashboard shows moderation alert for collaborator draft posts', function () {
    $this->actingAs(adminDashboardUser());

    $collaboratorRole = Role::findOrCreate('colaborador', 'web');
    $collaborator = User::factory()->create();
    $collaborator->assignRole($collaboratorRole);

    $editorRole = Role::findOrCreate('editor', 'web');
    $editor = User::factory()->create();
    $editor->assignRole($editorRole);

    Post::query()->create([
        'title' => 'Borrador colaborador pendiente',
        'slug' => 'borrador-colaborador-pendiente',
        'status' => 'draft',
        'created_by' => $collaborator->id,
        'updated_by' => $collaborator->id,
        'sort_order' => 1,
    ]);

    Post::query()->create([
        'title' => 'Borrador editor no moderable',
        'slug' => 'borrador-editor-no-moderable',
        'status' => 'draft',
        'created_by' => $editor->id,
        'updated_by' => $editor->id,
        'sort_order' => 2,
    ]);

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Moderacion de noticias')
        ->assertSee('1 pendiente(s)')
        ->assertSee('Borrador colaborador pendiente');
});

test('dashboard hides moderation alert for users without moderation permissions', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([
        Permission::findOrCreate('ViewAny:Post', 'web'),
        Permission::findOrCreate('View:Post', 'web'),
    ]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('Moderacion de noticias');
});

function adminDashboardUser(): User
{
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $user->assignRole(Role::findOrCreate('super_admin', 'web'));

    return $user;
}

/**
 * @param  array<string, mixed>  $overrides
 */
function createDashboardContract(array $overrides = []): Contract
{
    $contractType = ContractType::query()->create([
        'name' => 'Suministros '.fake()->unique()->numerify('##'),
        'slug' => 'suministros-dashboard-'.fake()->unique()->numerify('###'),
        'status' => 'published',
    ]);

    $defaults = [
        'process_code' => 'FSE-'.fake()->unique()->numerify('###').'-'.now()->year,
        'fiscal_year' => (int) now()->year,
        'contract_type_id' => $contractType->id,
        'object' => 'Adquisicion de insumos institucionales',
        'official_budget' => 12000000,
        'process_status' => 'en_curso',
        'status' => 'draft',
        'publication_date' => now()->toDateString(),
    ];

    return Contract::query()->create(array_merge($defaults, $overrides));
}
