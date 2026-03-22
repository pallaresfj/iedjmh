<?php

use App\Models\Contract;
use App\Models\ContractType;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('contract process code auto generates per fiscal year sequence', function () {
    $type = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-model',
        'status' => 'published',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-001-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Proceso uno',
        'process_status' => 'en_curso',
        'status' => 'draft',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-003-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Proceso tres',
        'process_status' => 'en_curso',
        'status' => 'draft',
    ]);

    $generated = Contract::query()->create([
        'process_code' => null,
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Proceso autogenerado',
        'process_status' => 'en_curso',
        'status' => 'draft',
    ]);

    expect($generated->process_code)->toBe('FSE-004-2026');
});

test('contract process code must be unique', function () {
    $type = ContractType::query()->create([
        'name' => 'Servicios',
        'slug' => 'servicios-model',
        'status' => 'published',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-100-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Proceso base',
        'process_status' => 'en_curso',
        'status' => 'draft',
    ]);

    expect(function () use ($type): void {
        Contract::query()->create([
            'process_code' => 'FSE-100-2026',
            'fiscal_year' => 2026,
            'contract_type_id' => $type->id,
            'object' => 'Proceso duplicado',
            'process_status' => 'en_curso',
            'status' => 'draft',
        ]);
    })->toThrow(QueryException::class);
});
