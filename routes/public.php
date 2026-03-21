<?php

use App\Http\Controllers\Public\AcademicController;
use App\Http\Controllers\Public\CitizenAttentionController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InstitutionController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('institucion')->group(function () {
    Route::get('/', [InstitutionController::class, 'index'])->name('institucion.index');
    Route::get('/historia', [InstitutionController::class, 'page'])->defaults('pageKey', 'historia')->name('institucion.historia');
    Route::get('/mision-vision', [InstitutionController::class, 'page'])->defaults('pageKey', 'mision-vision')->name('institucion.mision-vision');
    Route::get('/simbolos', [InstitutionController::class, 'page'])->defaults('pageKey', 'simbolos')->name('institucion.simbolos');
    Route::get('/equipo-directivo', [InstitutionController::class, 'page'])->defaults('pageKey', 'equipo-directivo')->name('institucion.equipo-directivo');
    Route::get('/sedes', [InstitutionController::class, 'page'])->defaults('pageKey', 'sedes')->name('institucion.sedes');
    Route::get('/pei', [InstitutionController::class, 'page'])->defaults('pageKey', 'pei')->name('institucion.pei');
    Route::get('/manual-convivencia', [InstitutionController::class, 'page'])->defaults('pageKey', 'manual-convivencia')->name('institucion.manual-convivencia');
    Route::get('/directorio', [InstitutionController::class, 'page'])->defaults('pageKey', 'directorio')->name('institucion.directorio');
});

Route::prefix('academico')->group(function () {
    Route::get('/', [AcademicController::class, 'index'])->name('academico.index');
    Route::get('/niveles-educativos', [AcademicController::class, 'page'])->defaults('pageKey', 'niveles-educativos')->name('academico.niveles-educativos');
    Route::get('/modalidad-agropecuaria', [AcademicController::class, 'page'])->defaults('pageKey', 'modalidad-agropecuaria')->name('academico.modalidad-agropecuaria');
    Route::get('/planes-area', [AcademicController::class, 'page'])->defaults('pageKey', 'planes-area')->name('academico.planes-area');
    Route::get('/sistema-evaluacion', [AcademicController::class, 'page'])->defaults('pageKey', 'sistema-evaluacion')->name('academico.sistema-evaluacion');
    Route::get('/proyectos-pedagogicos', [AcademicController::class, 'page'])->defaults('pageKey', 'proyectos-pedagogicos')->name('academico.proyectos-pedagogicos');
    Route::get('/calendario-academico', [AcademicController::class, 'page'])->defaults('pageKey', 'calendario-academico')->name('academico.calendario-academico');
});

Route::prefix('proyectos')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('proyectos.index');
    Route::get('/{slug}', [ProjectController::class, 'show'])->name('proyectos.show');
});

Route::prefix('noticias')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('noticias.index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('noticias.show');
});

Route::prefix('transparencia')->group(function () {
    Route::get('/', [TransparencyController::class, 'index'])->name('transparencia.index');
    Route::get('/documentos', [TransparencyController::class, 'documents'])->name('transparencia.documentos');
    Route::get('/documentos/{slug}', [TransparencyController::class, 'showDocument'])->name('transparencia.documento');
});

Route::prefix('eventos')->group(function () {
    Route::get('/{slug}', [EventController::class, 'show'])->name('eventos.show');
});

Route::prefix('atencion-ciudadano')->group(function () {
    Route::get('/', [CitizenAttentionController::class, 'index'])->name('atencion.index');
    Route::get('/contactenos', [CitizenAttentionController::class, 'contact'])->name('atencion.contactenos');
    Route::get('/pqrs', [CitizenAttentionController::class, 'pqrs'])->name('atencion.pqrs');
    Route::post('/pqrs', [CitizenAttentionController::class, 'submitPqrs'])
        ->middleware('throttle:pqrs')
        ->name('atencion.pqrs.store');
    Route::get('/tramites-servicios', [CitizenAttentionController::class, 'procedures'])->name('atencion.tramites');
    Route::get('/preguntas-frecuentes', [CitizenAttentionController::class, 'faqs'])->name('atencion.faq');
    Route::get('/mapa-sitio', [CitizenAttentionController::class, 'sitemap'])->name('atencion.mapa-sitio');
    Route::get('/participacion', [CitizenAttentionController::class, 'participation'])->name('atencion.participacion');
});
