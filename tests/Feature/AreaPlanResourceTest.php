<?php

use App\Filament\Resources\AreaPlans\AreaPlanResource;
use App\Filament\Resources\AreaPlans\Pages\CreateAreaPlan;
use App\Filament\Resources\AreaPlans\Pages\EditAreaPlan;
use App\Filament\Resources\AreaPlans\Pages\ListAreaPlans;
use App\Models\AreaPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('user with area plan permissions can list create edit and delete area plans', function () {
    $role = createRoleWithAreaPlanPermissions('administrador', ['ViewAny', 'View', 'Create', 'Update', 'Delete']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $existing = AreaPlan::query()->create([
        'area_name' => 'Matematicas',
        'responsible_teachers' => 'Docente Uno, Docente Dos',
        'icon' => 'calculate',
        'plan_url' => 'https://example.com/plan-matematicas',
        'status' => 'published',
        'sort_order' => 1,
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.area-plans.index'))
        ->assertOk()
        ->assertSee('Matematicas');

    Livewire::test(CreateAreaPlan::class)
        ->fillForm([
            'area_name' => 'Ciencias Naturales',
            'responsible_teachers' => 'Docente A, Docente B',
            'icon' => 'science',
            'plan_url' => 'https://example.com/plan-ciencias',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 2,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created = AreaPlan::query()
        ->where('area_name', 'Ciencias Naturales')
        ->firstOrFail();

    expect($created->plan_url)->toBe('https://example.com/plan-ciencias')
        ->and($created->icon)->toBe('science');

    Livewire::test(EditAreaPlan::class, ['record' => $created->getKey()])
        ->fillForm([
            'responsible_teachers' => 'Docente A, Docente B, Docente C',
            'status' => 'published',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $created->refresh();

    expect($created->responsible_teachers)->toContain('Docente C')
        ->and(AreaPlanResource::canDelete($created))->toBeTrue();

    Livewire::test(ListAreaPlans::class)
        ->assertCanSeeTableRecords([$existing, $created])
        ->callTableAction('delete', $created)
        ->assertHasNoTableActionErrors();

    expect(AreaPlan::query()->whereKey($created->getKey())->exists())->toBeFalse();
});

test('user without area plan permissions cannot access area plan resource', function () {
    $role = Role::findOrCreate('colaborador', 'web');
    $role->syncPermissions([]);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $record = AreaPlan::query()->create([
        'area_name' => 'Sociales',
        'responsible_teachers' => 'Docente Social',
        'icon' => 'history_edu',
        'plan_url' => 'https://example.com/plan-sociales',
        'status' => 'published',
        'sort_order' => 1,
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.area-plans.index'))
        ->assertForbidden();

    $this->get(route('filament.admin.resources.area-plans.edit', ['record' => $record]))
        ->assertForbidden();

    expect(AreaPlanResource::canViewAny())->toBeFalse()
        ->and(AreaPlanResource::canCreate())->toBeFalse()
        ->and(AreaPlanResource::canView($record))->toBeFalse()
        ->and(AreaPlanResource::canDelete($record))->toBeFalse();
});

/**
 * @param  array<int, string>  $abilities
 */
function createRoleWithAreaPlanPermissions(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:AreaPlan")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}
