<?php

use App\Filament\Resources\Contracts\Pages\CreateContract;
use App\Filament\Resources\Contracts\Pages\EditContract;
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
                    'external_url' => 'https://example.test/estudios.pdf',
                    'sort_order' => 1,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'invitacion_pliegos',
                    'external_url' => 'https://example.test/invitacion.pdf',
                    'sort_order' => 2,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'formato_propuesta',
                    'external_url' => 'https://example.test/propuesta.pdf',
                    'sort_order' => 3,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created = Contract::query()->with('documents')->firstOrFail();

    expect(Contract::query()->count())->toBe(1)
        ->and($created->documents)->toHaveCount(3)
        ->and($created->documents->pluck('title')->filter()->count())->toBe(3);

    FilamentNotification::assertNotNotified('No se pudo guardar el contrato');
});

test('contract edit shows clear notification when publication validation fails', function () {
    $user = userWithContractEditPermission();
    $contractType = publishedContractType();
    $contract = Contract::factory()->draft()->create([
        'contract_type_id' => $contractType->id,
        'process_status' => 'en_curso',
    ]);

    $this->actingAs($user);

    Livewire::test(EditContract::class, ['record' => $contract->getKey()])
        ->fillForm([
            'status' => 'published',
            'process_status' => 'en_curso',
            'documents' => [],
        ])
        ->call('save')
        ->assertHasErrors(['documents']);

    FilamentNotification::assertNotified('No se pudo guardar el contrato');

    expect($contract->fresh()->status)->toBe('draft');
});

test('contract edit accepts valid published payload with consistent documents', function () {
    $user = userWithContractEditPermission();
    $contractType = publishedContractType();
    $contract = Contract::factory()->draft()->create([
        'contract_type_id' => $contractType->id,
        'process_status' => 'en_curso',
    ]);

    $this->actingAs($user);

    Livewire::test(EditContract::class, ['record' => $contract->getKey()])
        ->fillForm([
            'status' => 'published',
            'process_status' => 'en_curso',
            'documents' => [
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'estudios_previos',
                    'external_url' => 'https://example.test/estudios.pdf',
                    'sort_order' => 1,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'invitacion_pliegos',
                    'external_url' => 'https://example.test/invitacion.pdf',
                    'sort_order' => 2,
                ],
                [
                    'stage' => 'convocatoria',
                    'document_type' => 'formato_propuesta',
                    'external_url' => 'https://example.test/propuesta.pdf',
                    'sort_order' => 3,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $updated = $contract->fresh('documents');

    expect($updated->status)->toBe('published')
        ->and($updated->documents)->toHaveCount(3);

    FilamentNotification::assertNotNotified('No se pudo guardar el contrato');
});

test('contract edit accepts valid save and normalizes invalid document stage', function () {
    $user = userWithContractEditPermission();
    $contractType = publishedContractType();
    $contract = Contract::factory()->draft()->create([
        'contract_type_id' => $contractType->id,
        'process_status' => 'en_curso',
    ]);
    $contract->documents()->create([
        'stage' => 'etapa_invalida',
        'document_type' => 'estudios_previos',
        'title' => 'Estudios previos',
        'external_url' => 'https://example.test/estudios-invalido.pdf',
        'sort_order' => 0,
    ]);

    $this->actingAs($user);

    Livewire::test(EditContract::class, ['record' => $contract->getKey()])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $updated = $contract->fresh('documents');
    $estudiosPrevios = $updated->documents->first();

    expect($updated->status)->toBe('draft')
        ->and($updated->documents)->toHaveCount(1)
        ->and($estudiosPrevios?->stage)->toBe('convocatoria');

    FilamentNotification::assertNotNotified('No se pudo guardar el contrato');
});

test('contract edit clears document type when stage changes to an incompatible option', function () {
    $user = userWithContractEditPermission();
    $contractType = publishedContractType();
    $contract = Contract::factory()->create([
        'contract_type_id' => $contractType->id,
        'process_status' => 'en_curso',
        'status' => 'draft',
        'published_at' => null,
    ]);
    $contract->documents()->create([
        'stage' => 'convocatoria',
        'document_type' => 'estudios_previos',
        'title' => 'Estudios previos',
        'external_url' => 'https://example.test/estudios.pdf',
        'sort_order' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test(EditContract::class, ['record' => $contract->getKey()])
        ->set('data.documents.0.document_type', 'estudios_previos')
        ->set('data.documents.0.stage', 'adjudicacion')
        ->assertSet('data.documents.0.document_type', null);
});

function userWithContractCreatePermission(): User
{
    return userWithContractPermissions(['ViewAny', 'Create']);
}

function userWithContractEditPermission(): User
{
    return userWithContractPermissions(['ViewAny', 'View', 'Update']);
}

/**
 * @param  array<int, string>  $abilities
 */
function userWithContractPermissions(array $abilities): User
{
    $role = Role::findOrCreate('contratos-editor', 'web');
    $permissions = collect($abilities)
        ->unique()
        ->map(fn (string $ability): string => "{$ability}:Contract")
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
