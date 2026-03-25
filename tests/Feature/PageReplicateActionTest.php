<?php

use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('pages table replicate action duplicates record safely with unique slug and null menu binding', function () {
    $user = createPageReplicator();
    $this->actingAs($user);

    $original = Page::query()->create([
        'title' => 'Pagina Historia',
        'slug' => 'pagina-historia',
        'menu_binding' => 'institucion.historia',
        'summary' => 'Resumen base institucional',
        'content' => '<p>Contenido base institucional.</p>',
        'status' => 'published',
    ]);

    Page::query()->create([
        'title' => 'Slug ocupado',
        'slug' => 'pagina-historia-copia',
        'menu_binding' => null,
        'status' => 'draft',
    ]);

    Livewire::test(ListPages::class)
        ->assertTableActionExists('replicate', null, $original)
        ->callTableAction('replicate', $original)
        ->assertHasNoTableActionErrors();

    expect(Page::query()->count())->toBe(3);

    $replica = Page::query()
        ->whereKeyNot($original->getKey())
        ->orderByDesc('id')
        ->first();

    expect($replica)->not->toBeNull()
        ->and($replica?->status)->toBe('draft')
        ->and($replica?->menu_binding)->toBeNull()
        ->and($replica?->slug)->toBe('pagina-historia-copia-2')
        ->and($replica?->title)->toBe(Str::limit($original->title.' (copia)', 255, ''))
        ->and($replica?->summary)->toBe($original->summary)
        ->and($replica?->content)->toBe($original->content);
});

function createPageReplicator(): User
{
    $role = Role::findOrCreate('administrador', 'web');

    $permissions = collect([
        'ViewAny:Page',
        'View:Page',
        'Create:Page',
        'Replicate:Page',
    ])->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
