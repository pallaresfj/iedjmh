<?php

use App\Models\Contract;
use App\Models\ContractParticipant;
use App\Models\ContractType;
use App\Models\Document;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPublishedContract(array $attributes = []): Contract
{
    $type = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-'.fake()->unique()->numerify('###'),
        'status' => 'published',
    ]);

    return Contract::query()->create(array_merge([
        'process_code' => 'FSE-001-2026-'.fake()->unique()->numerify('###'),
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Adquisicion de insumos',
        'official_budget' => 15000000,
        'process_status' => 'en_curso',
        'status' => 'published',
        'published_at' => now(),
        'publication_date' => now()->toDateString(),
    ], $attributes));
}

test('contracting index and detail only expose published contracts', function () {
    $type = ContractType::query()->create([
        'name' => 'Mantenimiento',
        'slug' => 'mantenimiento-publico',
        'status' => 'published',
    ]);

    $published = Contract::query()->create([
        'process_code' => 'FSE-001-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Mantenimiento electrico',
        'process_status' => 'en_curso',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $draft = Contract::query()->create([
        'process_code' => 'FSE-002-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Mantenimiento hidraulico',
        'process_status' => 'en_curso',
        'status' => 'draft',
    ]);

    $this->get(route('transparencia.contratacion.index'))
        ->assertOk()
        ->assertSee($published->process_code)
        ->assertDontSee($draft->process_code);

    $this->get(route('transparencia.contratacion.show', ['processCode' => $published->process_code]))
        ->assertOk()
        ->assertSee($published->object);

    $this->get(route('transparencia.contratacion.show', ['processCode' => $draft->process_code]))
        ->assertNotFound();
});

test('contracting filters by year status type and search term', function () {
    $suppliesType = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-filtro',
        'status' => 'published',
    ]);

    $servicesType = ContractType::query()->create([
        'name' => 'Prestacion de Servicios',
        'slug' => 'servicios-filtro',
        'status' => 'published',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-001-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $suppliesType->id,
        'object' => 'Adquisicion de insumos agropecuarios',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
        'contractor_name' => 'Proveedor Uno',
        'contractor_nit' => '900111111-1',
        'contractor_social_object' => 'Suministro de insumos',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-005-2025',
        'fiscal_year' => 2025,
        'contract_type_id' => $servicesType->id,
        'object' => 'Asesoria tecnica agroindustrial',
        'process_status' => 'en_curso',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('transparencia.contratacion.index', ['fiscal_year' => '2026']))
        ->assertOk()
        ->assertSee('FSE-001-2026')
        ->assertDontSee('FSE-005-2025');

    $this->get(route('transparencia.contratacion.index', ['process_status' => 'adjudicado']))
        ->assertOk()
        ->assertSee('FSE-001-2026')
        ->assertDontSee('FSE-005-2025');

    $this->get(route('transparencia.contratacion.index', ['type' => 'servicios-filtro']))
        ->assertOk()
        ->assertSee('FSE-005-2025')
        ->assertDontSee('FSE-001-2026');

    $this->get(route('transparencia.contratacion.index', ['q' => 'insumos']))
        ->assertOk()
        ->assertSee('FSE-001-2026')
        ->assertDontSee('FSE-005-2025');
});

test('contracting index shows active filters summary and contextual no results copy', function () {
    $type = ContractType::query()->create([
        'name' => 'Mantenimiento',
        'slug' => 'mantenimiento-filtro-activo',
        'status' => 'published',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-030-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Mantenimiento de laboratorios',
        'process_status' => 'en_curso',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('transparencia.contratacion.index', ['process_status' => 'adjudicado']))
        ->assertOk()
        ->assertSee('Filtros activos')
        ->assertSee('Estado:')
        ->assertSee('Adjudicado')
        ->assertSee('Limpia los filtros para ver todos los procesos publicados.');
});

test('contracting page renders manual document from settings when available', function () {
    $manualDocument = Document::query()->create([
        'title' => 'Manual de Contratacion Vigente',
        'slug' => 'manual-contratacion-vigente',
        'status' => 'published',
        'external_url' => 'https://drive.google.com/file/d/1manualcontratacionvigente/view?usp=sharing',
        'published_at' => now(),
    ]);

    Setting::query()->create([
        'institution_name' => 'IED Prueba',
        'singleton' => 1,
        'contracting_manual_document_id' => $manualDocument->id,
    ]);

    $contract = createPublishedContract([
        'process_code' => 'FSE-010-2026',
    ]);

    $this->get(route('transparencia.contratacion.index'))
        ->assertOk()
        ->assertSee('Manual de Contratacion Vigente')
        ->assertSee('https://drive.google.com/file/d/1manualcontratacionvigente/view?usp=sharing', false)
        ->assertSee($contract->process_code);
});

test('contracting page shows directory entries from adjudicated contracts', function () {
    $type = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-directorio',
        'status' => 'published',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-020-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Compra de semillas',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
        'contractor_name' => 'Proveedor Verde SAS',
        'contractor_nit' => '900999999-1',
        'contractor_social_object' => 'Comercializacion de insumos agropecuarios',
    ]);

    Contract::query()->create([
        'process_code' => 'FSE-021-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Mantenimiento de aulas',
        'process_status' => 'en_curso',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('transparencia.contratacion.index'))
        ->assertOk()
        ->assertSee('Directorio de contratistas adjudicados')
        ->assertSee('Proveedor Verde SAS')
        ->assertSee('900999999-1')
        ->assertSee('Comercializacion de insumos agropecuarios');
});

test('contracting detail shows participants list and awarded marker', function () {
    $type = ContractType::query()->create([
        'name' => 'Suministros',
        'slug' => 'suministros-detalle-participantes',
        'status' => 'published',
    ]);

    $contract = Contract::query()->create([
        'process_code' => 'FSE-022-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Compra de equipos',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
    ]);

    ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'name' => 'Proveedor Ganador SAS',
        'nit' => '900123123-4',
        'social_object' => 'Comercializacion de equipos',
        'evaluation_score' => 95.25,
        'is_awarded' => true,
    ]);

    ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'name' => 'Proveedor Alterno SAS',
        'nit' => '900456456-7',
        'social_object' => 'Suministros tecnologicos',
        'evaluation_score' => 89.10,
        'is_awarded' => false,
    ]);

    $this->get(route('transparencia.contratacion.show', ['processCode' => $contract->process_code]))
        ->assertOk()
        ->assertSee('Proveedor Ganador SAS')
        ->assertSee('95,25')
        ->assertSee('Adjudicado')
        ->assertSee('Proveedor Alterno SAS');
});

test('contracting directory prioritizes awarded participant over legacy contract fields', function () {
    $type = ContractType::query()->create([
        'name' => 'Servicios',
        'slug' => 'servicios-directorio-ganador',
        'status' => 'published',
    ]);

    $contract = Contract::query()->create([
        'process_code' => 'FSE-023-2026',
        'fiscal_year' => 2026,
        'contract_type_id' => $type->id,
        'object' => 'Servicios de soporte',
        'process_status' => 'adjudicado',
        'status' => 'published',
        'published_at' => now(),
        'contractor_name' => 'Dato Legacy',
        'contractor_nit' => '111111111-1',
        'contractor_social_object' => 'Dato legacy social',
    ]);

    ContractParticipant::query()->create([
        'contract_id' => $contract->id,
        'name' => 'Ganador Real SAS',
        'nit' => '900777777-0',
        'social_object' => 'Servicios profesionales',
        'evaluation_score' => 92,
        'is_awarded' => true,
    ]);

    $this->get(route('transparencia.contratacion.index'))
        ->assertOk()
        ->assertSee('Ganador Real SAS')
        ->assertSee('900777777-0')
        ->assertDontSee('Dato Legacy');
});

test('public navigation contains contratacion item', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Contratación')
        ->assertSee(route('transparencia.contratacion.index'), false);
});
