<?php

use App\Models\Contract;
use App\Models\ContractParticipant;
use App\Models\ContractType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('awarded participant syncs winner snapshot into contract fields', function () {
    $type = ContractType::query()->create([
        'name' => 'Servicios',
        'slug' => 'servicios-sync-winner',
        'status' => 'published',
    ]);

    $contract = Contract::query()->create([
        'process_code' => 'FSE-100-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Servicio tecnico especializado',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
    ]);

    ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'name' => 'Ganador Principal SAS',
        'nit' => '900100100-1',
        'social_object' => 'Servicios tecnicos',
        'is_awarded' => true,
    ]);

    $contract->refresh();

    expect($contract->contractor_name)->toBe('Ganador Principal SAS')
        ->and($contract->contractor_nit)->toBe('900100100-1')
        ->and($contract->contractor_social_object)->toBe('Servicios tecnicos');
});

test('sync updates winner snapshot when awarded participant changes', function () {
    $type = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-sync-winner-change',
        'status' => 'published',
    ]);

    $contract = Contract::query()->create([
        'process_code' => 'FSE-101-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Adquisicion de herramientas',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $first = ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'name' => 'Proveedor Uno SAS',
        'nit' => '900200200-2',
        'social_object' => 'Suministros generales',
        'is_awarded' => true,
    ]);

    $second = ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'name' => 'Proveedor Dos SAS',
        'nit' => '900300300-3',
        'social_object' => 'Suministros especializados',
        'is_awarded' => false,
    ]);

    $first->update(['is_awarded' => false]);
    $second->update(['is_awarded' => true]);

    $contract->refresh();

    expect($contract->contractor_name)->toBe('Proveedor Dos SAS')
        ->and($contract->contractor_nit)->toBe('900300300-3')
        ->and($contract->contractor_social_object)->toBe('Suministros especializados');
});
