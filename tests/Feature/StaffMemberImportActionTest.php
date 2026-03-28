<?php

use App\Filament\Resources\StaffMembers\Pages\ListStaffMembers;
use App\Models\Campus;
use App\Models\StaffMember;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as SpreadsheetWorksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('staff import action processes xlsx rows with create update defaults and failures', function () {
    $role = createStaffRoleWithAbilities('administrador-personal-import', ['ViewAny', 'View', 'Create']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $principalCampus = Campus::query()->create([
        'name' => 'Sede Principal',
        'slug' => 'sede-principal',
        'status' => 'published',
        'sort_order' => 0,
    ]);

    StaffMember::query()->create([
        'full_name' => 'Registro existente',
        'position_title' => 'Rector anterior',
        'department_label' => 'Rectoria',
        'staff_group' => 'directive',
        'campus_id' => $principalCampus->id,
        'institutional_email' => 'rectoria@iedjmh.edu.co',
        'phone' => '3000000000',
        'profile_photo_path' => 'staff-members/existente.jpg',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'sort_order' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $file = makeStaffImportXlsxWithSheets([
        [
            'title' => 'Worksheet',
            'rows' => [
                ['Nombre completo *', 'Cargo *', 'Dependencia', 'Tipo de personal', 'Sede (slug)', 'Correo institucional', 'Telefono', 'Estado', 'Fecha de publicacion', 'Orden'],
                ['Ana Lucia Martinez', 'Rectora', 'Rectoria', 'directive', 'sede-principal', 'ana.martinez@iedjmh.edu.co', '3010001111', 'published', '', 2],
                ['Registro existente actualizado', 'Rector', 'Rectoria', 'directive', 'sede-principal', 'RECTORIA@IEDJMH.EDU.CO', '3020002222', 'published', '', 5],
                ['', 'Docente sin nombre', 'Academico', 'teacher', 'sede-no-existe', 'fila.invalida@iedjmh.edu.co', '', 'draft', '', 0],
                ['Carlos Perez', 'Docente', '', '', '', 'carlos.perez@iedjmh.edu.co', '', '', '', ''],
            ],
        ],
        [
            'title' => 'Hoja2',
            'rows' => [
                [null, null, null, null],
                ['Nombre cualquiera', null, 'usuario', '=C2&"@iedagropivijay.edu.co"'],
            ],
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(ListStaffMembers::class)
        ->assertActionExists('downloadStaffMembersTemplate')
        ->assertActionExists('importStaffMembers')
        ->callAction('importStaffMembers', data: ['file' => $file])
        ->assertHasNoActionErrors();

    expect(StaffMember::query()->count())->toBe(3);

    $createdPublished = StaffMember::query()
        ->where('institutional_email', 'ana.martinez@iedjmh.edu.co')
        ->firstOrFail();

    expect($createdPublished->full_name)->toBe('Ana Lucia Martinez')
        ->and($createdPublished->staff_group)->toBe('directive')
        ->and($createdPublished->status)->toBe('published')
        ->and($createdPublished->published_at)->not->toBeNull()
        ->and($createdPublished->campus_id)->toBe($principalCampus->id)
        ->and($createdPublished->created_by)->toBe($user->id)
        ->and($createdPublished->updated_by)->toBe($user->id);

    $updatedExisting = StaffMember::query()
        ->where('institutional_email', 'rectoria@iedjmh.edu.co')
        ->firstOrFail();

    expect($updatedExisting->full_name)->toBe('Registro existente actualizado')
        ->and($updatedExisting->position_title)->toBe('Rector')
        ->and($updatedExisting->phone)->toBe('3020002222')
        ->and($updatedExisting->sort_order)->toBe(5)
        ->and($updatedExisting->profile_photo_path)->toBe('staff-members/existente.jpg')
        ->and($updatedExisting->updated_by)->toBe($user->id);

    $createdWithDefaults = StaffMember::query()
        ->where('institutional_email', 'carlos.perez@iedjmh.edu.co')
        ->firstOrFail();

    expect($createdWithDefaults->staff_group)->toBe('directive')
        ->and($createdWithDefaults->status)->toBe('draft')
        ->and($createdWithDefaults->sort_order)->toBe(0)
        ->and($createdWithDefaults->published_at)->toBeNull()
        ->and($createdWithDefaults->campus_id)->toBeNull();

    expect(
        StaffMember::query()
            ->where('institutional_email', 'fila.invalida@iedjmh.edu.co')
            ->exists()
    )->toBeFalse();
});

test('staff import action detects valid sheet when first sheet is invalid', function () {
    $role = createStaffRoleWithAbilities('administrador-personal-import-detector', ['ViewAny', 'View', 'Create']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    Campus::query()->create([
        'name' => 'Sede Principal',
        'slug' => 'sede-principal',
        'status' => 'published',
        'sort_order' => 0,
    ]);

    $file = makeStaffImportXlsxWithSheets([
        [
            'title' => 'Hoja2',
            'rows' => [
                [null, null, null],
                ['Nasly Luz Borja Fontalvo', null, 'nborja'],
            ],
        ],
        [
            'title' => 'Worksheet',
            'rows' => [
                ['Nombre completo *', 'Cargo *', 'Dependencia', 'Tipo de personal', 'Sede (slug)', 'Correo institucional', 'Telefono', 'Estado', 'Fecha de publicacion', 'Orden'],
                ['Maria Alejandra Diaz', 'Docente', 'Academico', 'teacher', 'sede-principal', 'maria.diaz@iedjmh.edu.co', '3050003333', 'published', '', 1],
            ],
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(ListStaffMembers::class)
        ->callAction('importStaffMembers', data: ['file' => $file])
        ->assertHasNoActionErrors();

    expect(
        StaffMember::query()
            ->where('institutional_email', 'maria.diaz@iedjmh.edu.co')
            ->exists()
    )->toBeTrue();
});

test('staff import action does not import rows when workbook has no valid sheet', function () {
    $role = createStaffRoleWithAbilities('administrador-personal-import-invalido', ['ViewAny', 'View', 'Create']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $file = makeStaffImportXlsxWithSheets([
        [
            'title' => 'HojaA',
            'rows' => [
                [null, null, null],
                ['Solo texto', 'sin', 'encabezados'],
            ],
        ],
        [
            'title' => 'HojaB',
            'rows' => [
                ['Usuario', 'Alias', 'Correo'],
                ['Nasly Luz Borja Fontalvo', 'nborja', 'nborja@iedagropivijay.edu.co'],
            ],
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(ListStaffMembers::class)
        ->callAction('importStaffMembers', data: ['file' => $file])
        ->assertHasNoActionErrors();

    expect(StaffMember::query()->count())->toBe(0);
});

test('staff import action is hidden when user cannot create staff records', function () {
    $role = createStaffRoleWithAbilities('visor-personal-import', ['ViewAny', 'View']);

    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    $this->actingAs($user);

    Livewire::test(ListStaffMembers::class)
        ->assertActionHidden('importStaffMembers');
});

/**
 * @param  array<int, string>  $abilities
 */
function createStaffRoleWithAbilities(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:StaffMember")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}

/**
 * @param  array<int, array<int|string, mixed>>  $rows
 */
function makeStaffImportXlsx(array $rows): UploadedFile
{
    return makeStaffImportXlsxWithSheets([
        [
            'title' => 'Worksheet',
            'rows' => $rows,
        ],
    ]);
}

/**
 * @param  array<int, array{title: string, rows: array<int, array<int|string|null>>}>  $sheets
 */
function makeStaffImportXlsxWithSheets(array $sheets): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->removeSheetByIndex(0);

    foreach ($sheets as $index => $sheetDefinition) {
        $worksheet = new SpreadsheetWorksheet($spreadsheet, $sheetDefinition['title']);
        $spreadsheet->addSheet($worksheet, $index);

        foreach ($sheetDefinition['rows'] as $rowIndex => $rowData) {
            $worksheet->fromArray($rowData, null, 'A'.($rowIndex + 1), true);
        }
    }

    $spreadsheet->setActiveSheetIndex(0);

    $writer = new Xlsx($spreadsheet);

    ob_start();
    $writer->save('php://output');
    $binary = (string) ob_get_clean();

    $spreadsheet->disconnectWorksheets();

    return UploadedFile::fake()->createWithContent('personal-import.xlsx', $binary);
}
