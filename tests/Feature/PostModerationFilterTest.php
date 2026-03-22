<?php

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('posts table filter pending moderation only shows collaborator drafts', function () {
    $collaboratorRole = createModerationRoleWithPostPermissions('colaborador', ['ViewAny', 'View', 'Create']);
    $editorRole = createModerationRoleWithPostPermissions('editor', ['ViewAny', 'View', 'Create', 'Update']);

    $collaborator = User::factory()->create();
    $collaborator->assignRole($collaboratorRole);

    $editor = User::factory()->create();
    $editor->assignRole($editorRole);

    $pendingModeration = Post::query()->create([
        'title' => 'Borrador colaborador pendiente',
        'slug' => 'borrador-colaborador-pendiente-filtro',
        'status' => 'draft',
        'sort_order' => 1,
        'created_by' => $collaborator->id,
        'updated_by' => $collaborator->id,
    ]);

    $editorDraft = Post::query()->create([
        'title' => 'Borrador editor',
        'slug' => 'borrador-editor-filtro',
        'status' => 'draft',
        'sort_order' => 2,
        'created_by' => $editor->id,
        'updated_by' => $editor->id,
    ]);

    $publishedPost = Post::query()->create([
        'title' => 'Publicada colaborador',
        'slug' => 'publicada-colaborador-filtro',
        'status' => 'published',
        'published_at' => now()->subHour(),
        'sort_order' => 3,
        'created_by' => $collaborator->id,
        'updated_by' => $collaborator->id,
    ]);

    $this->actingAs($editor);

    Livewire::test(ListPosts::class)
        ->assertTableFilterExists('pending_moderation')
        ->assertCanSeeTableRecords([$pendingModeration, $editorDraft, $publishedPost])
        ->filterTable('pending_moderation')
        ->assertCanSeeTableRecords([$pendingModeration])
        ->assertCanNotSeeTableRecords([$editorDraft, $publishedPost]);
});

/**
 * @param  array<int, string>  $abilities
 */
function createModerationRoleWithPostPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:Post")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
