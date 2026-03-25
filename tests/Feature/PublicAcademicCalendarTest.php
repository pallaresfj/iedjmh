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

test('academic calendar paginates events with 5 items per page', function () {
    foreach (range(1, 6) as $index) {
        Event::query()->create([
            'title' => "Evento paginado {$index}",
            'slug' => "evento-paginado-{$index}",
            'status' => 'published',
            'starts_at' => now()->addDays($index)->setTime(8, 0),
            'published_at' => now(),
        ]);
    }

    $this->get(route('academico.calendario-academico'))
        ->assertOk()
        ->assertSee('Evento paginado 1')
        ->assertSee('Evento paginado 5')
        ->assertDontSee('Evento paginado 6')
        ->assertSee('page=2', false)
        ->assertSee('data-public-pagination-link', false);

    $this->get(route('academico.calendario-academico', ['page' => 2]))
        ->assertOk()
        ->assertSee('Evento paginado 6')
        ->assertDontSee('Evento paginado 1');
});

test('academic calendar keeps month filter in pagination links', function () {
    $monthDate = now()->addMonth()->startOfMonth()->setTime(9, 0);
    $monthFilter = $monthDate->format('Y-m');

    foreach (range(1, 6) as $index) {
        Event::query()->create([
            'title' => "Evento mes filtrado {$index}",
            'slug' => "evento-mes-filtrado-{$index}",
            'status' => 'published',
            'starts_at' => $monthDate->copy()->addDays($index),
            'published_at' => now(),
        ]);
    }

    $this->get(route('academico.calendario-academico', ['month' => $monthFilter]))
        ->assertOk()
        ->assertSee('Evento mes filtrado 1')
        ->assertDontSee('Evento mes filtrado 6')
        ->assertSee('page=2', false)
        ->assertSee('month='.$monthFilter, false)
        ->assertSee('data-public-pagination-link', false);

    $this->get(route('academico.calendario-academico', ['month' => $monthFilter, 'page' => 2]))
        ->assertOk()
        ->assertSee('Evento mes filtrado 6')
        ->assertDontSee('Evento mes filtrado 1');
});
