<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('graduate auth page renders enhanced preregistration submit button and dropzone', function () {
    $this->get(route('egresados.index'))
        ->assertOk()
        ->assertSee('Iniciar Verificación de Datos')
        ->assertSee('data-file-dropzone-root', false)
        ->assertSee('data-file-dropzone', false)
        ->assertSee('Soporta PDF, JPG, JPEG, PNG y WEBP (Máx. 1MB)');
});

