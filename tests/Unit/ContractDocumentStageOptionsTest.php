<?php

use App\Models\ContractDocument;

test('document type options are filtered by stage', function () {
    $convocatoria = ContractDocument::documentTypeOptionsForStage('convocatoria');
    $adjudicacion = ContractDocument::documentTypeOptionsForStage('adjudicacion');
    $soporte = ContractDocument::documentTypeOptionsForStage('soporte');

    expect(array_keys($convocatoria))
        ->toBe(['estudios_previos', 'invitacion_pliegos', 'formato_propuesta', 'otro'])
        ->and(array_keys($adjudicacion))
        ->toBe(['acta_cierre', 'informe_evaluacion', 'acto_adjudicacion', 'otro'])
        ->and(array_keys($soporte))
        ->toBe(['otro']);
});

test('document type and stage compatibility validator enforces catalog rules', function () {
    expect(ContractDocument::isDocumentTypeAllowedForStage('convocatoria', 'estudios_previos'))->toBeTrue()
        ->and(ContractDocument::isDocumentTypeAllowedForStage('adjudicacion', 'estudios_previos'))->toBeFalse()
        ->and(ContractDocument::isDocumentTypeAllowedForStage('soporte', 'otro'))->toBeTrue()
        ->and(ContractDocument::isDocumentTypeAllowedForStage('soporte', 'acta_cierre'))->toBeFalse()
        ->and(ContractDocument::isDocumentTypeAllowedForStage(null, 'otro'))->toBeFalse()
        ->and(ContractDocument::isDocumentTypeAllowedForStage('convocatoria', null))->toBeFalse();
});
