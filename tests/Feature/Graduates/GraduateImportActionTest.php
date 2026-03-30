<?php

use App\Filament\Resources\Graduates\Pages\ListGraduates;
use App\Models\Graduate;
use App\Models\GraduateDocument;
use App\Models\User;
use App\Rules\GoogleDriveUrl;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as SpreadsheetWorksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('graduate import action creates and updates records by national id', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $user->givePermissionTo(Permission::findOrCreate('Create:Graduate', 'web'));

    Graduate::factory()->create([
        'national_id' => '1234567890',
        'full_name' => 'Registro Antiguo',
        'email' => 'old@example.com',
        'status' => 'preloaded',
    ]);

    $file = makeGraduateImportXlsx([
        ['Identificacion nacional *', 'Nombre completo *', 'Ano de graduacion *', 'Correo', 'Telefono', 'Ocupacion actual', 'Ciudad', 'Pais', 'Estado', 'Titulo academico', 'Fecha de grado', 'Acta', 'Folio', 'Verificacion'],
        ['1234567890', 'Registro Actualizado', '2023', 'old@example.com', '3000001111', 'Especialista', 'Pivijay', 'Colombia', 'active', 'Tecnico', '2023-12-20', 'ACT-01', 'FOL-01', 'verified'],
        ['9999999999', 'Nuevo Egresado', '2022', 'new@example.com', '3000002222', 'Analista', 'Santa Marta', 'Colombia', 'preloaded', 'Tecnologo', '2022-12-15', 'ACT-02', 'FOL-02', 'pending'],
    ]);

    $this->actingAs($user);

    Livewire::test(ListGraduates::class)
        ->callAction('importGraduates', data: ['file' => $file])
        ->assertHasNoActionErrors();

    expect(Graduate::query()->count())->toBe(2);

    $updated = Graduate::query()->where('national_id', '1234567890')->firstOrFail();
    expect($updated->full_name)->toBe('Registro Actualizado')
        ->and($updated->status)->toBe('active')
        ->and($updated->record_verification_status)->toBe('verified');

    $created = Graduate::query()->where('national_id', '9999999999')->firstOrFail();
    expect($created->full_name)->toBe('Nuevo Egresado')
        ->and($created->status)->toBe('preloaded');
});

test('google drive rule rejects non google urls', function () {
    $fails = Validator::make([
        'drive_url' => 'https://example.com/file.pdf',
    ], [
        'drive_url' => [new GoogleDriveUrl],
    ])->fails();

    $passes = Validator::make([
        'drive_url' => 'https://drive.google.com/file/d/123/view',
    ], [
        'drive_url' => [new GoogleDriveUrl],
    ])->passes();

    expect($fails)->toBeTrue()
        ->and($passes)->toBeTrue();
});

test('graduate import action is visible only for users with create permission', function () {
    $managerRole = createGraduateRoleWithAbilities('gestor-egresados-import', ['ViewAny', 'View', 'Create']);
    $viewerRole = createGraduateRoleWithAbilities('visor-egresados-import', ['ViewAny', 'View']);

    $manager = User::factory()->create([
        'is_admin' => false,
    ]);
    $manager->assignRole($managerRole);

    $viewer = User::factory()->create([
        'is_admin' => false,
    ]);
    $viewer->assignRole($viewerRole);

    $this->actingAs($manager);
    Livewire::test(ListGraduates::class)
        ->assertActionVisible('importGraduates');

    $this->actingAs($viewer);
    Livewire::test(ListGraduates::class)
        ->assertActionHidden('importGraduates');
});

test('graduate document supports file source without google drive url', function () {
    $graduate = Graduate::factory()->create();

    $document = GraduateDocument::factory()->create([
        'graduate_id' => $graduate->id,
        'drive_url' => null,
        'file_path' => 'graduates/documents/test-identificacion.pdf',
        'file_disk' => 'local',
    ]);

    expect($document->file_path)->toBe('graduates/documents/test-identificacion.pdf')
        ->and($document->drive_url)->toBeNull();
});

test('graduate document requires either drive url or file source', function () {
    $graduate = Graduate::factory()->create();

    expect(function () use ($graduate): void {
        GraduateDocument::factory()->create([
            'graduate_id' => $graduate->id,
            'drive_url' => null,
            'file_path' => null,
            'file_disk' => null,
        ]);
    })->toThrow(ValidationException::class);
});

/**
 * @param  array<int, string>  $abilities
 */
function createGraduateRoleWithAbilities(string $roleName, array $abilities): Role
{
    $role = Role::findOrCreate($roleName, 'web');

    $permissions = collect($abilities)
        ->map(fn (string $ability): string => "{$ability}:Graduate")
        ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'))
        ->all();

    $role->syncPermissions($permissions);

    return $role;
}

/**
 * @param  array<int, array<int, string>>  $rows
 */
function makeGraduateImportXlsx(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->removeSheetByIndex(0);

    $worksheet = new SpreadsheetWorksheet($spreadsheet, 'Worksheet');
    $spreadsheet->addSheet($worksheet, 0);

    foreach ($rows as $rowIndex => $rowData) {
        $worksheet->fromArray($rowData, null, 'A'.($rowIndex + 1), true);
    }

    $spreadsheet->setActiveSheetIndex(0);
    $writer = new Xlsx($spreadsheet);

    ob_start();
    $writer->save('php://output');
    $binary = (string) ob_get_clean();
    $spreadsheet->disconnectWorksheets();

    return UploadedFile::fake()->createWithContent('egresados-import.xlsx', $binary);
}
