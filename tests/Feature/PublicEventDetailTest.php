<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public event detail page renders published event information', function () {
    $event = Event::query()->create([
        'title' => 'Feria Agropecuaria Escolar',
        'slug' => 'feria-agropecuaria-escolar',
        'summary' => 'Evento institucional para la comunidad.',
        'description' => 'Detalle del evento agropecuario.',
        'location' => 'Sede principal',
        'starts_at' => now()->addDays(5)->setTime(8, 0),
        'ends_at' => now()->addDays(5)->setTime(12, 0),
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('eventos.show', ['slug' => $event->slug]))
        ->assertOk()
        ->assertSee('Informacion del evento')
        ->assertSee($event->title)
        ->assertSee($event->summary)
        ->assertSee($event->description);
});

test('home upcoming events link to event detail page', function () {
    $event = Event::query()->create([
        'title' => 'Encuentro de Semilleros',
        'slug' => 'encuentro-de-semilleros',
        'starts_at' => now()->addDays(3)->setTime(9, 30),
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('eventos.show', ['slug' => $event->slug]), false);
});

test('public event detail renders related events with time and location metadata', function () {
    $mainEvent = Event::query()->create([
        'title' => 'Foro Institucional',
        'slug' => 'foro-institucional',
        'summary' => 'Evento principal.',
        'starts_at' => now()->addDays(4)->setTime(8, 0),
        'status' => 'published',
        'published_at' => now(),
    ]);

    $relatedEvent = Event::query()->create([
        'title' => 'Feria Tecnologica y del Emprendimiento',
        'slug' => 'feria-tecnologica-emprendimiento',
        'starts_at' => now()->addDays(6)->setTime(9, 0),
        'ends_at' => now()->addDays(6)->setTime(14, 0),
        'location' => 'Polideportivo Institucional',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Event::query()->create([
        'title' => 'Jornada Pedagogica',
        'slug' => 'jornada-pedagogica-relacionada',
        'starts_at' => now()->addDays(8)->setTime(7, 0),
        'ends_at' => now()->addDays(8)->setTime(13, 0),
        'location' => 'Sede Colegio Agropecuario',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('eventos.show', ['slug' => $mainEvent->slug]))
        ->assertOk()
        ->assertSee('Otros eventos')
        ->assertSee($relatedEvent->title)
        ->assertSee('09:00 AM - 02:00 PM')
        ->assertSee('Polideportivo Institucional')
        ->assertSee('public-home-event-item__date--highlight', false)
        ->assertSee('public-home-event-item__date--default', false);
});

test('draft events are not publicly accessible by detail route', function () {
    $event = Event::query()->create([
        'title' => 'Evento en borrador',
        'slug' => 'evento-borrador',
        'starts_at' => now()->addDays(10)->setTime(10, 0),
        'status' => 'draft',
    ]);

    $this->get(route('eventos.show', ['slug' => $event->slug]))
        ->assertNotFound();
});
