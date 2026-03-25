<?php

use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Models\Banner;
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

test('banner table replicate action duplicates record as draft with null slug', function () {
    $user = createBannerReplicator();
    $this->actingAs($user);

    $page = Page::query()->create([
        'title' => 'Pagina de historia',
        'slug' => 'pagina-historia',
        'status' => 'published',
    ]);

    $original = Banner::query()->create([
        'title' => 'Banner institucional principal',
        'slug' => 'banner-institucional-principal',
        'page_id' => $page->id,
        'subtitle' => 'Comunidad educativa',
        'description' => 'Descripcion del banner original.',
        'cta_label' => 'Conocer mas',
        'cta_url' => 'https://ied.example.edu/banner',
        'target' => '_blank',
        'status' => 'published',
    ]);

    Livewire::test(ListBanners::class)
        ->assertTableActionExists('replicate', null, $original)
        ->callTableAction('replicate', $original)
        ->assertHasNoTableActionErrors();

    expect(Banner::query()->count())->toBe(2);

    $replica = Banner::query()
        ->whereKeyNot($original->getKey())
        ->latest('id')
        ->first();

    expect($replica)->not->toBeNull()
        ->and($replica?->status)->toBe('draft')
        ->and($replica?->slug)->toBeNull()
        ->and($replica?->title)->toBe(Str::limit($original->title.' (copia)', 255, ''))
        ->and($replica?->page_id)->toBe($original->page_id)
        ->and($replica?->subtitle)->toBe($original->subtitle)
        ->and($replica?->description)->toBe($original->description)
        ->and($replica?->cta_label)->toBe($original->cta_label)
        ->and($replica?->cta_url)->toBe($original->cta_url)
        ->and($replica?->target)->toBe($original->target);
});

function createBannerReplicator(): User
{
    $role = Role::findOrCreate('administrador', 'web');

    $permissions = collect([
        'ViewAny:Banner',
        'View:Banner',
        'Create:Banner',
        'Replicate:Banner',
    ])->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
