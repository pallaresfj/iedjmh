<?php

use App\Support\Contracts\ContractPublicationValidator;

test('validator requires convocatoria documents when publishing en curso', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'en_curso',
        'documents' => [],
    ]);

    expect($errors)->toHaveKey('documents');
});

test('validator requires adjudication data for adjudicado contracts', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'adjudicado',
        'documents' => [
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/a.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/c.pdf'],
        ],
    ]);

    expect($errors)
        ->toHaveKey('documents')
        ->toHaveKey('participants');
});

test('validator accepts a complete adjudicado publication payload', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'adjudicado',
        'documents' => [
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/a.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/c.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'acta_cierre', 'external_url' => 'https://example.test/d.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'informe_evaluacion', 'external_url' => 'https://example.test/e.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'acto_adjudicacion', 'external_url' => 'https://example.test/f.pdf'],
        ],
        'participants' => [
            [
                'name' => 'Proveedor Uno',
                'nit' => '900111111-1',
                'social_object' => 'Suministro de insumos',
                'is_awarded' => true,
            ],
            [
                'name' => 'Proveedor Dos',
                'nit' => '900222222-2',
                'social_object' => 'Servicios logisticos',
                'is_awarded' => false,
            ],
        ],
    ]);

    expect($errors)->toBe([]);
});

test('validator rejects duplicated official document types', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'en_curso',
        'documents' => [
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/a.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/c.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/d.pdf'],
        ],
    ]);

    expect($errors)->toHaveKey('documents');
});

test('validator rejects documents without external url', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'en_curso',
        'documents' => [
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/c.pdf'],
        ],
    ]);

    expect($errors)->toHaveKey('documents.0.external_url');
});

test('validator requires a single awarded participant for adjudicado publication', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'adjudicado',
        'documents' => [
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/a.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/c.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'acta_cierre', 'external_url' => 'https://example.test/d.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'informe_evaluacion', 'external_url' => 'https://example.test/e.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'acto_adjudicacion', 'external_url' => 'https://example.test/f.pdf'],
        ],
        'participants' => [
            ['name' => 'Proveedor Uno', 'nit' => '900111111-1', 'social_object' => 'Suministros', 'is_awarded' => false],
            ['name' => 'Proveedor Dos', 'nit' => '900222222-2', 'social_object' => 'Servicios', 'is_awarded' => false],
        ],
    ]);

    expect($errors)->toHaveKey('participants_awarded');
});

test('validator rejects duplicated participants by nit', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'adjudicado',
        'documents' => [
            ['stage' => 'convocatoria', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/a.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/c.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'acta_cierre', 'external_url' => 'https://example.test/d.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'informe_evaluacion', 'external_url' => 'https://example.test/e.pdf'],
            ['stage' => 'adjudicacion', 'document_type' => 'acto_adjudicacion', 'external_url' => 'https://example.test/f.pdf'],
        ],
        'participants' => [
            ['name' => 'Proveedor Uno', 'nit' => '900.111.111-1', 'social_object' => 'Suministros', 'is_awarded' => true],
            ['name' => 'Proveedor Dos', 'nit' => '9001111111', 'social_object' => 'Servicios', 'is_awarded' => false],
        ],
    ]);

    expect($errors)->toHaveKey('participants.1.nit');
});

test('validator rejects incompatible document stage and type combinations', function () {
    $errors = ContractPublicationValidator::validate([
        'status' => 'published',
        'process_status' => 'en_curso',
        'documents' => [
            ['stage' => 'adjudicacion', 'document_type' => 'estudios_previos', 'external_url' => 'https://example.test/a.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'invitacion_pliegos', 'external_url' => 'https://example.test/b.pdf'],
            ['stage' => 'convocatoria', 'document_type' => 'formato_propuesta', 'external_url' => 'https://example.test/c.pdf'],
        ],
    ]);

    expect($errors)->toHaveKey('documents.0.stage');
});
