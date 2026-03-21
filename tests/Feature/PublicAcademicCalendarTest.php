<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('academic calendar page renders search and month filters without agenda institucional heading', function () {
    Event::query()->create([
        'title' => 'Jornada pedagogica institucional',
        'slug' => 'jornada-pedagogica-institucional',
        'status' => 'published',
        'starts_at' => now()->addDays(10)->setTime(8, 0),
        'published_at' => now(),
    ]);

    $this->get(route('academico.calendario-academico'))
        ->assertOk()
        ->assertDontSee('Agenda institucional')
        ->assertSee('Cajon de busqueda')
        ->assertSee('Filtro por fecha');
});

test('academic calendar filters events by search and month', function () {
    $firstEventDate = now()->addMonth()->startOfMonth()->setTime(9, 0);
    $secondEventDate = now()->addMonths(2)->startOfMonth()->setTime(10, 0);

    Event::query()->create([
        'title' => 'Socializacion de resultados periodicos',
        'slug' => 'socializacion-resultados-periodicos',
        'status' => 'published',
        'starts_at' => $firstEventDate,
        'published_at' => now(),
    ]);

    Event::query()->create([
        'title' => 'Encuentro de familias y docentes',
        'slug' => 'encuentro-familias-docentes',
        'status' => 'published',
        'starts_at' => $secondEventDate,
        'published_at' => now(),
    ]);

    $this->get(route('academico.calendario-academico', ['q' => 'familias']))
        ->assertOk()
        ->assertSee('Encuentro de familias y docentes')
        ->assertDontSee('Socializacion de resultados periodicos');

    $this->get(route('academico.calendario-academico', ['month' => $firstEventDate->format('Y-m')]))
        ->assertOk()
        ->assertSee('Socializacion de resultados periodicos')
        ->assertDontSee('Encuentro de familias y docentes');
});
