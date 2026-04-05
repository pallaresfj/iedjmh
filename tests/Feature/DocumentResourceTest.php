<?php

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Models\Document;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('document resource enforces google drive link validation', function () {
    $role = createRoleWithDocumentPermissions('administrador', ['ViewAny', 'View', 'Create', 'Update', 'Delete']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $this->actingAs($user);

    Livewire::test(CreateDocument::class)
        ->fillForm([
            'title' => 'Documento con URL invalida',
            'slug' => 'documento-con-url-invalida',
            'external_url' => 'https://example.com/documento.pdf',
            'status' => 'draft',
            'sort_order' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['external_url']);

    Livewire::test(CreateDocument::class)
        ->fillForm([
            'title' => 'Documento sin URL',
            'slug' => 'documento-sin-url',
            'external_url' => '',
            'status' => 'draft',
            'sort_order' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['external_url']);

    Livewire::test(CreateDocument::class)
        ->fillForm([
            'title' => 'Documento Drive Valido',
            'slug' => 'documento-drive-valido',
            'description' => '<p><strong>Descripcion</strong> enriquecida.</p>',
            'external_url' => 'https://drive.google.com/file/d/1documentovalido/view?usp=sharing',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created = Document::query()->where('slug', 'documento-drive-valido')->firstOrFail();

    expect($created->document_number)->toBe('DOC - 0001')
        ->and($created->description)->toContain('<strong>Descripcion</strong>');
});

test('document resource allows overriding suggested document number', function () {
    $role = createRoleWithDocumentPermissions('administrador-document-number', ['ViewAny', 'View', 'Create', 'Update', 'Delete']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $this->actingAs($user);

    Livewire::test(CreateDocument::class)
        ->fillForm([
            'title' => 'Documento con numero manual',
            'slug' => 'documento-numero-manual',
            'external_url' => 'https://drive.google.com/file/d/1manualnumber/view?usp=sharing',
            'document_number' => 'DOC - 0099',
            'status' => 'draft',
            'sort_order' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created = Document::query()->where('slug', 'documento-numero-manual')->firstOrFail();

    expect($created->document_number)->toBe('DOC - 0099');
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithDocumentPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:Document")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
