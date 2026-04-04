<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sitemap xml excludes retired institution directorio route', function () {
    $this->get(route('sitemap.xml'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
        ->assertDontSee('/institucion/directorio');
});

test('sitemap xml keeps pei and manual convivencia routes', function () {
    $this->get(route('sitemap.xml'))
        ->assertOk()
        ->assertSee('/institucion/pei')
        ->assertSee('/institucion/manual-convivencia');
});
