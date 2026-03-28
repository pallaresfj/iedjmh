<?php

use App\Models\Contract;
use App\Models\Contractor;
use App\Models\ContractParticipant;
use App\Models\ContractType;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('contractor nit must be unique', function () {
    Contractor::query()->create([
        'name' => 'Proveedor A SAS',
        'nit' => '900111111-1',
        'social_object' => 'Suministro general',
        'is_active' => true,
    ]);

    expect(fn () => Contractor::query()->create([
        'name' => 'Proveedor B SAS',
        'nit' => '900111111-1',
        'social_object' => 'Servicios generales',
        'is_active' => true,
    ]))->toThrow(QueryException::class);
});

test('participant keeps editable snapshot without mutating contractor master data', function () {
    $contractor = Contractor::query()->create([
        'name' => 'Proveedor Maestro SAS',
        'nit' => '900222222-2',
        'social_object' => 'Objeto social maestro',
        'is_active' => true,
    ]);

    $type = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-catalogo',
        'status' => 'published',
    ]);

    $contract = Contract::query()->create([
        'process_code' => 'FSE-200-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Compra de materiales',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $participant = ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'contractor_id' => $contractor->id,
        'name' => 'Proveedor Maestro SAS - Sede Norte',
        'nit' => '900222222-2',
        'social_object' => 'Objeto social adaptado al proceso',
        'is_awarded' => true,
    ]);

    expect($participant->fresh()->contractor?->name)->toBe('Proveedor Maestro SAS')
        ->and($participant->name)->toBe('Proveedor Maestro SAS - Sede Norte')
        ->and($contractor->fresh()->social_object)->toBe('Objeto social maestro');
});

test('winner sync uses participant snapshot instead of contractor master data', function () {
    $contractor = Contractor::query()->create([
        'name' => 'Proveedor Base SAS',
        'nit' => '900333333-3',
        'social_object' => 'Objeto social base',
        'is_active' => true,
    ]);

    $type = ContractType::query()->create([
        'name' => 'Servicios',
        'slug' => 'servicios-catalogo',
        'status' => 'published',
    ]);

    $contract = Contract::query()->create([
        'process_code' => 'FSE-201-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Servicio especializado',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
    ]);

    ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'contractor_id' => $contractor->id,
        'name' => 'Proveedor Base SAS - Union Temporal',
        'nit' => '900333333-3',
        'social_object' => 'Objeto social ajustado para este proceso',
        'is_awarded' => true,
    ]);

    $contract->refresh();

    expect($contract->contractor_name)->toBe('Proveedor Base SAS - Union Temporal')
        ->and($contract->contractor_social_object)->toBe('Objeto social ajustado para este proceso')
        ->and($contractor->fresh()->social_object)->toBe('Objeto social base');
});
