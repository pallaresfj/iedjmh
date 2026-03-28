<?php

use App\Filament\Resources\PqrsRequests\Pages\CreatePqrsRequest;
use App\Filament\Resources\PqrsRequests\Pages\EditPqrsRequest;
use App\Filament\Resources\PqrsRequests\Pages\ListPqrsRequests;
use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use App\Models\PqrsRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('user with pqrs permissions can list create edit and delete pqrs requests', function () {
    $role = createRoleWithPqrsPermissions('colaborador', ['ViewAny', 'View', 'Create', 'Update', 'Delete']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $existing = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-MANAGE-001',
        'type' => 'peticion',
        'status' => 'received',
        'priority' => 'medium',
        'subject' => 'Solicitud existente para listar',
        'message' => str_repeat('Contenido existente. ', 3),
        'applicant_name' => 'Solicitante inicial',
        'submitted_at' => now()->subHours(2),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.pqrs-requests.index'))
        ->assertOk()
        ->assertSee('Solicitud existente para listar');

    Livewire::test(CreatePqrsRequest::class)
        ->fillForm([
            'type' => 'queja',
            'status' => 'received',
            'priority' => 'high',
            'subject' => 'Nueva PQRS creada desde panel',
            'message' => str_repeat('Mensaje detallado para la solicitud. ', 3),
            'applicant_name' => 'Mariana Torres',
            'applicant_email' => 'mariana@example.test',
            'applicant_phone' => '3000000000',
            'applicant_document' => '111222333',
            'applicant_address' => 'Calle 1 #2-3',
            'municipality' => 'Pivijay',
            'consent_habeas_data' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created = PqrsRequest::query()
        ->where('subject', 'Nueva PQRS creada desde panel')
        ->firstOrFail();

    expect($created->tracking_code)->toStartWith('PQRS-'.now()->format('Y').'-')
        ->and($created->created_by)->toBe($user->id)
        ->and($created->updated_by)->toBe($user->id);

    Livewire::test(EditPqrsRequest::class, ['record' => $created->getKey()])
        ->fillForm([
            'status' => 'in_process',
            'internal_notes' => 'En revision por oficina de atencion.',
            'assigned_to' => $user->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created->refresh();

    expect($created->status)->toBe('in_process')
        ->and($created->internal_notes)->toContain('En revision')
        ->and($created->assigned_to)->toBe($user->id)
        ->and($created->updated_by)->toBe($user->id)
        ->and(PqrsRequestResource::canDelete($created))->toBeTrue();

    Livewire::test(ListPqrsRequests::class)
        ->assertCanSeeTableRecords([$existing, $created])
        ->callTableAction('delete', $created)
        ->assertHasNoTableActionErrors();

    expect(PqrsRequest::query()->whereKey($created->getKey())->exists())->toBeFalse();
});

test('user without pqrs permissions cannot access pqrs resource', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-BLOCK-001',
        'type' => 'peticion',
        'status' => 'received',
        'priority' => 'medium',
        'subject' => 'Solicitud bloqueada',
        'message' => str_repeat('Contenido bloqueado. ', 3),
        'applicant_name' => 'Usuario bloqueado',
        'submitted_at' => now()->subHour(),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.pqrs-requests.index'))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.pqrs-requests.view', ['record' => $record]))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.pqrs-requests.edit', ['record' => $record]))
        ->assertForbidden();

    expect(PqrsRequestResource::canViewAny())->toBeFalse()
        ->and(PqrsRequestResource::canCreate())->toBeFalse()
        ->and(PqrsRequestResource::canView($record))->toBeFalse()
        ->and(PqrsRequestResource::canDelete($record))->toBeFalse();
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithPqrsPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:PqrsRequest")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
