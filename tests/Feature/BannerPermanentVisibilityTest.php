<?php

use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Models\Banner;
use App\Models\Page;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('creating a permanent banner stores ends_at as null and keeps starts_at', function () {
    $user = createBannerManager();
    $this->actingAs($user);

    $startsAt = now()->addDay()->startOfHour()->format('Y-m-d H:i');

    Livewire::test(CreateBanner::class)
        ->fillForm([
            'title' => 'Banner permanente home',
            'slug' => 'banner-permanente-home',
            'status' => 'published',
            'target' => '_self',
            'starts_at' => $startsAt,
            'is_permanent' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $banner = Banner::query()
        ->where('slug', 'banner-permanente-home')
        ->firstOrFail();

    expect($banner->starts_at?->format('Y-m-d H:i'))->toBe($startsAt)
        ->and($banner->ends_at)->toBeNull();
});

test('editing a banner and enabling permanent clears ends_at', function () {
    $user = createBannerManager();
    $this->actingAs($user);

    $banner = Banner::query()->create([
        'title' => 'Banner con vencimiento',
        'slug' => 'banner-con-vencimiento',
        'status' => 'published',
        'target' => '_self',
        'starts_at' => now()->subDay()->startOfHour(),
        'ends_at' => now()->addDays(5)->startOfHour(),
    ]);

    Livewire::test(EditBanner::class, ['record' => $banner->getKey()])
        ->fillForm([
            'is_permanent' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $banner->refresh();

    expect($banner->ends_at)->toBeNull();
});

test('editing a banner allows linking an existing page from the select field', function () {
    $user = createBannerManager();
    $this->actingAs($user);

    $page = Page::query()->create([
        'title' => 'Planes de Area',
        'slug' => 'academico-planes-area',
        'status' => 'published',
    ]);

    $banner = Banner::query()->create([
        'title' => 'Banner sin pagina',
        'slug' => 'banner-sin-pagina',
        'status' => 'published',
        'target' => '_self',
    ]);

    Livewire::test(EditBanner::class, ['record' => $banner->getKey()])
        ->fillForm([
            'page_id' => $page->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $banner->refresh();

    expect($banner->page_id)->toBe($page->id);
});

function createBannerManager(): User
{
    $role = Role::findOrCreate('administrador', 'web');

    $permissions = collect([
        'ViewAny:Banner',
        'View:Banner',
        'Create:Banner',
        'Update:Banner',
    ])->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
