<?php

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('project gallery rejects images larger than 2MB', function () {
    Storage::fake('public');

    $role = createRoleWithProjectPermissions('editor-proyectos', ['ViewAny', 'View', 'Create', 'Update']);
    $user = User::factory()->create(['is_admin' => false]);
    $user->assignRole($role);

    $this->actingAs($user);

    Livewire::test(CreateProject::class)
        ->fillForm([
            'title' => 'Proyecto con galeria pesada',
            'slug' => 'proyecto-con-galeria-pesada',
            'status' => 'draft',
            'sort_order' => 0,
            'gallery_image_paths' => [
                UploadedFile::fake()->image('pesada.jpg')->size(2500),
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['gallery_image_paths']);

    expect(Project::query()->where('slug', 'proyecto-con-galeria-pesada')->exists())->toBeFalse();
});

test('project gallery accepts images up to 2MB', function () {
    Storage::fake('public');

    $role = createRoleWithProjectPermissions('editor-proyectos-ok', ['ViewAny', 'View', 'Create', 'Update']);
    $user = User::factory()->create(['is_admin' => false]);
    $user->assignRole($role);

    $this->actingAs($user);

    Livewire::test(CreateProject::class)
        ->fillForm([
            'title' => 'Proyecto con galeria valida',
            'slug' => 'proyecto-con-galeria-valida',
            'status' => 'draft',
            'sort_order' => 0,
            'gallery_image_paths' => [
                UploadedFile::fake()->image('valida.jpg')->size(1900),
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    expect(Project::query()->where('slug', 'proyecto-con-galeria-valida')->exists())->toBeTrue();
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithProjectPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:Project")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
