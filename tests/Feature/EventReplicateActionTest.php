<?php

use App\Filament\Resources\Events\Pages\ListEvents;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('events table replicate action duplicates record as draft with unique slug and replicated categories', function () {
    $user = createEventReplicator();
    $this->actingAs($user);

    $categoryOne = Category::query()->create([
        'name' => 'Institucional',
        'slug' => 'institucional',
        'status' => 'published',
    ]);

    $categoryTwo = Category::query()->create([
        'name' => 'Academico',
        'slug' => 'academico',
        'status' => 'published',
    ]);

    $original = Event::query()->create([
        'title' => 'Encuentro de Semilleros',
        'slug' => 'encuentro-de-semilleros',
        'summary' => 'Resumen del evento original.',
        'description' => 'Descripcion extendida del evento original.',
        'location' => 'Polideportivo',
        'starts_at' => now()->addDays(5)->setTime(9, 0),
        'ends_at' => now()->addDays(5)->setTime(14, 0),
        'is_all_day' => false,
        'registration_url' => 'https://ied.example.edu/eventos/semilleros',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $original->categories()->sync([
        $categoryOne->getKey() => ['sort_order' => 3],
        $categoryTwo->getKey() => ['sort_order' => 7],
    ]);

    $occupiedSlugRecord = Event::query()->create([
        'title' => 'Slug ocupado',
        'slug' => 'encuentro-de-semilleros-copia',
        'status' => 'draft',
        'starts_at' => now()->addDays(1),
    ]);
    $occupiedSlugRecord->delete();

    Livewire::test(ListEvents::class)
        ->assertTableActionExists('replicate', null, $original)
        ->callTableAction('replicate', $original)
        ->assertHasNoTableActionErrors();

    $replica = Event::query()
        ->whereKeyNot($original->getKey())
        ->orderByDesc('id')
        ->first();

    expect($replica)->not->toBeNull()
        ->and($replica?->status)->toBe('draft')
        ->and($replica?->published_at)->toBeNull()
        ->and($replica?->title)->toBe(Str::limit($original->title.' (copia)', 255, ''))
        ->and($replica?->slug)->toBe('encuentro-de-semilleros-copia-2')
        ->and($replica?->summary)->toBe($original->summary)
        ->and($replica?->description)->toBe($original->description)
        ->and($replica?->location)->toBe($original->location)
        ->and($replica?->registration_url)->toBe($original->registration_url)
        ->and($replica?->is_all_day)->toBe($original->is_all_day)
        ->and($replica?->starts_at?->toDateString())->toBe($original->starts_at?->toDateString())
        ->and($replica?->ends_at?->toDateString())->toBe($original->ends_at?->toDateString())
        ->and($replica?->ends_at?->diffInMinutes($replica?->starts_at))->toBe($original->ends_at?->diffInMinutes($original->starts_at));

    $replicaCategorySortOrders = $replica
        ?->categories()
        ->get()
        ->mapWithKeys(fn (Category $category): array => [
            (string) $category->getKey() => (int) ($category->pivot?->sort_order ?? 0),
        ])
        ->all();

    expect($replicaCategorySortOrders)->toBe([
        (string) $categoryOne->getKey() => 3,
        (string) $categoryTwo->getKey() => 7,
    ]);
});

function createEventReplicator(): User
{
    $role = Role::findOrCreate('administrador', 'web');

    $permissions = collect([
        'ViewAny:Event',
        'View:Event',
        'Create:Event',
        'Replicate:Event',
    ])->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
