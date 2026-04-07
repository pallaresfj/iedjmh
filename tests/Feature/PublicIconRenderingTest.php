<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

test('public icon component renders material symbols and legacy values', function () {
    $material = Blade::render('<x-public.icon icon="ms:agriculture" class="text-2xl" />');
    $legacy = Blade::render('<x-public.icon icon="agriculture" class="text-2xl" />');

    expect($material)->toContain('material-symbols-outlined')
        ->and($material)->toContain('>agriculture</span>')
        ->and($legacy)->toContain('material-symbols-outlined')
        ->and($legacy)->toContain('>agriculture</span>');
});

test('public icon component renders font awesome icon classes', function () {
    $html = Blade::render('<x-public.icon icon="fa:solid:house" class="text-2xl" />');

    expect($html)->toContain('fa-solid fa-house')
        ->and($html)->toContain('public-icon--fa');
});

test('public icon component falls back safely when icon is invalid', function () {
    $html = Blade::render('<x-public.icon icon="fa:ultra:house" class="text-2xl" />');

    expect($html)->toContain('material-symbols-outlined')
        ->and($html)->toContain('>help</span>');
});

test('home navigation renders font awesome icon from settings', function () {
    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Iconos FA',
        'academic_modality_label' => 'Modalidad Flexible',
        'academic_modality_icon' => 'fa:solid:leaf',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('fa-solid fa-leaf', false)
        ->assertSee('Modalidad Flexible');
});
