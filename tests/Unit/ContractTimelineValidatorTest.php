<?php

use App\Support\Contracts\ContractTimelineValidator;

test('timeline validator accepts consistent process dates', function () {
    $errors = ContractTimelineValidator::validate([
        'publication_date' => '2026-03-01',
        'offers_deadline_date' => '2026-03-05',
        'evaluation_date' => '2026-03-08',
        'award_date' => '2026-03-10',
    ]);

    expect($errors)->toBe([]);
});

test('timeline validator rejects inconsistent process dates', function () {
    $errors = ContractTimelineValidator::validate([
        'publication_date' => '2026-03-10',
        'offers_deadline_date' => '2026-03-05',
        'evaluation_date' => '2026-03-04',
        'award_date' => '2026-03-03',
    ]);

    expect($errors)
        ->toHaveKey('offers_deadline_date')
        ->toHaveKey('evaluation_date')
        ->toHaveKey('award_date');
});

test('timeline validator allows partial dates when sequence is respected', function () {
    $errors = ContractTimelineValidator::validate([
        'publication_date' => '2026-03-01',
        'offers_deadline_date' => '2026-03-05',
        'evaluation_date' => '',
        'award_date' => null,
    ]);

    expect($errors)->toBe([]);
});

test('timeline validator requires previous stages when setting later dates', function () {
    $errors = ContractTimelineValidator::validate([
        'publication_date' => '2026-03-01',
        'offers_deadline_date' => null,
        'evaluation_date' => null,
        'award_date' => '2026-03-10',
    ]);

    expect($errors)
        ->toHaveKey('award_date')
        ->and($errors['award_date'])->toContain('evaluación');
});
