<?php

use App\Filament\Resources\Contracts\Pages\CreateContract;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('contract create shows clear notification when publication validation fails', function () {
    $user = userWithContractCreatePermission();
    $contractType = publishedContractType();

    $this->actingAs($user);

    Livewire::test(CreateContract::class)
        ->fillForm([
            'fiscal_year' => 2026,
            'contract_type_id' => $contractType->id,
            'object' => 'Suministro de material pedagogico',
            'status' => 'published',
            'process_status' => 'en_curso',
        ])
        ->call('create')
        ->assertHasErrors(['documents']);

    FilamentNotification::assertNotified('No se pudo guardar el contrato');
});

test('contract create rejects inconsistent stage and type combinations', function () {
    $user = userWithContractCreatePermission();
    $contractType = publishedContractType();

    $this->actingAs($user);

    Livewire::test(CreateContract::class)
        ->fillForm([
            'fiscal_year' => 2026,
            'contract_type_id' => $contractType->id,
            'object' => 'Suministro de material pedagogico',
            'status' => 'published',
            'process_status' => 'en_curso',
            'documents' => [
                [
                    'stage' => 'adjudicacion',
                    'document_type' => 'estudios_previos',
                    'title' => 'Estudios previos',
                    'external_url' => 'https://example.test/estudios.pdf',
                    'sort_order' => 1,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'invitacion_pliegos',
                    'title' => 'Invitacion',
                    'external_url' => 'https://example.test/invitacion.pdf',
                    'sort_order' => 2,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'formato_propuesta',
                    'title' => 'Formato',
                    'external_url' => 'https://example.test/propuesta.pdf',
                    'sort_order' => 3,
                ],
            ],
        ])
        ->call('create')
        ->assertHasErrors();

    FilamentNotification::assertNotified('No se pudo guardar el contrato');
    expect(Contract::query()->count())->toBe(0);
});

test('contract create accepts valid published payload with consistent documents', function () {
    $user = userWithContractCreatePermission();
    $contractType = publishedContractType();

    $this->actingAs($user);

    Livewire::test(CreateContract::class)
        ->fillForm([
            'fiscal_year' => 2026,
            'contract_type_id' => $contractType->id,
            'object' => 'Suministro de material pedagogico',
            'status' => 'published',
            'process_status' => 'en_curso',
            'documents' => [
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'estudios_previos',
                    'title' => 'Estudios previos',
                    'external_url' => 'https://example.test/estudios.pdf',
                    'sort_order' => 1,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'invitacion_pliegos',
                    'title' => 'Invitacion',
                    'external_url' => 'https://example.test/invitacion.pdf',
                    'sort_order' => 2,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'formato_propuesta',
                    'title' => 'Formato',
                    'external_url' => 'https://example.test/propuesta.pdf',
                    'sort_order' => 3,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    expect(Contract::query()->count())->toBe(1);
    FilamentNotification::assertNotNotified('No se pudo guardar el contrato');
});

function userWithContractCreatePermission(): User
{
    $role = Role::findOrCreate('contratos-editor', 'web');
    $permissions = collect(['ViewAny:Contract', 'Create:Contract'])
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();
    $role->syncPermissions($permissions);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    return $user;
}

function publishedContractType(): ContractType
{
    return ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-'.fake()->unique()->numerify('###'),
        'status' => 'published',
        'sort_order' => 1,
    ]);
}
