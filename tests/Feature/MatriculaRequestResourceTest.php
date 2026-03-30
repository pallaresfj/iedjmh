<?php

use App\Filament\Resources\MatriculaRequests\MatriculaRequestResource;
use App\Filament\Resources\MatriculaRequests\Pages\EditMatriculaRequest;
use App\Filament\Resources\MatriculaRequests\Pages\ListMatriculaRequests;
use App\Models\Campus;
use App\Models\MatriculaRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('user with matricula permissions can list view edit and delete matricula requests', function () {
    $role = createRoleWithMatriculaPermissions('soporte', ['ViewAny', 'View', 'Update', 'Delete']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $campus = Campus::factory()->create();

    $record = MatriculaRequest::query()->create([
        'student_name' => 'Andrea Suarez',
        'grade' => 'primero',
        'document_number' => '1234567890',
        'phone' => '3000000000',
        'campus_id' => $campus->id,
        'status' => 'pending',
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.matricula-requests.index'))
        ->assertOk()
        ->assertSee('Andrea Suarez');

    $this->get(route('filament.admin.resources.matricula-requests.view', ['record' => $record]))
        ->assertOk();

    Livewire::test(EditMatriculaRequest::class, ['record' => $record->getKey()])
        ->fillForm([
            'status' => 'in_review',
            'internal_notes' => 'Documentacion validada en primera revision.',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $record->refresh();

    expect($record->status)->toBe('in_review')
        ->and($record->reviewed_at)->not->toBeNull()
        ->and($record->internal_notes)->toContain('validada')
        ->and(MatriculaRequestResource::canViewAny())->toBeTrue()
        ->and(MatriculaRequestResource::canView($record))->toBeTrue()
        ->and(MatriculaRequestResource::canEdit($record))->toBeTrue()
        ->and(MatriculaRequestResource::canDelete($record))->toBeTrue();

    Livewire::test(ListMatriculaRequests::class)
        ->assertCanSeeTableRecords([$record])
        ->callTableAction('delete', $record)
        ->assertHasNoTableActionErrors();

    expect(MatriculaRequest::query()->whereKey($record->getKey())->exists())->toBeFalse();
});

test('user without matricula permissions cannot access matricula resource', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = MatriculaRequest::factory()->create();

    $this->actingAs($user)
        ->get(route('filament.admin.resources.matricula-requests.index'))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.matricula-requests.view', ['record' => $record]))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.matricula-requests.edit', ['record' => $record]))
        ->assertForbidden();

    expect(MatriculaRequestResource::canViewAny())->toBeFalse()
        ->and(MatriculaRequestResource::canView($record))->toBeFalse()
        ->and(MatriculaRequestResource::canEdit($record))->toBeFalse()
        ->and(MatriculaRequestResource::canDelete($record))->toBeFalse();
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithMatriculaPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:MatriculaRequest")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
