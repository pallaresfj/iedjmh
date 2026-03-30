<?php

use App\Http\Controllers\Public\AcademicController;
use App\Http\Controllers\Public\CitizenAttentionController;
use App\Http\Controllers\Public\ContractingController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\Graduates\AuthController as GraduateAuthController;
use App\Http\Controllers\Public\Graduates\PanelController as GraduatePanelController;
use App\Http\Controllers\Public\Graduates\PasswordController as GraduatePasswordController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InstitutionController;
use App\Http\Controllers\Public\MatriculaController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapXmlController;
use App\Http\Controllers\Public\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/buscar', SearchController::class)->name('buscar');
Route::get('/sitemap.xml', SitemapXmlController::class)->name('sitemap.xml');
Route::get('/matricula', [MatriculaController::class, 'index'])->name('matricula.index');
Route::post('/matricula', [MatriculaController::class, 'store'])
    ->middleware('throttle:matricula')
    ->name('matricula.store');

Route::prefix('egresados')->name('egresados.')->group(function () {
    Route::get('/', [GraduateAuthController::class, 'index'])->name('index');
    Route::get('/login', fn () => redirect()->route('egresados.index'))->name('login.view');
    Route::post('/login', [GraduateAuthController::class, 'login'])
        ->middleware('throttle:20,1')
        ->name('login');
    Route::post('/preregistro', [GraduateAuthController::class, 'preregister'])
        ->middleware('throttle:20,1')
        ->name('preregister');

    Route::get('/olvido-clave', [GraduatePasswordController::class, 'create'])->name('password.request');
    Route::post('/olvido-clave', [GraduatePasswordController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('password.email');
    Route::get('/restablecer-clave/{token}', [GraduatePasswordController::class, 'edit'])->name('password.reset.form');
    Route::post('/restablecer-clave', [GraduatePasswordController::class, 'update'])->name('password.reset.update');

    Route::middleware(['graduate.auth', 'graduate.active'])->group(function (): void {
        Route::post('/logout', [GraduateAuthController::class, 'logout'])->name('logout');

        Route::prefix('panel')->name('panel.')->group(function (): void {
            Route::get('/', fn () => redirect()->route('egresados.panel.resumen'))->name('index');
            Route::get('/resumen', [GraduatePanelController::class, 'summary'])->name('resumen');
            Route::get('/mis-documentos', [GraduatePanelController::class, 'certificates'])->name('documentos');
            Route::get('/mis-certificados', fn () => redirect()->route('egresados.panel.documentos'))->name('certificados');
            Route::get('/registro-academico', [GraduatePanelController::class, 'academicRecord'])->name('registro-academico');
            Route::get('/configuracion', [GraduatePanelController::class, 'settings'])->name('configuracion');
            Route::patch('/configuracion', [GraduatePanelController::class, 'updateSettings'])->name('configuracion.update');
        });
    });
});

Route::prefix('institucion')->group(function () {
    Route::get('/', [InstitutionController::class, 'index'])->name('institucion.index');
    Route::get('/historia', [InstitutionController::class, 'page'])->defaults('pageKey', 'historia')->name('institucion.historia');
    Route::get('/mision-vision', [InstitutionController::class, 'page'])->defaults('pageKey', 'mision-vision')->name('institucion.mision-vision');
    Route::get('/simbolos', [InstitutionController::class, 'page'])->defaults('pageKey', 'simbolos')->name('institucion.simbolos');
    Route::get('/equipo-institucional', [InstitutionController::class, 'page'])->defaults('pageKey', 'equipo-institucional')->name('institucion.equipo-institucional');
    Route::get('/sedes', [InstitutionController::class, 'page'])->defaults('pageKey', 'sedes')->name('institucion.sedes');
    Route::get('/pei', [InstitutionController::class, 'page'])->defaults('pageKey', 'pei')->name('institucion.pei');
    Route::get('/manual-convivencia', [InstitutionController::class, 'page'])->defaults('pageKey', 'manual-convivencia')->name('institucion.manual-convivencia');
    Route::get('/directorio', [InstitutionController::class, 'page'])->defaults('pageKey', 'directorio')->name('institucion.directorio');
});

Route::prefix('academico')->group(function () {
    Route::get('/', [AcademicController::class, 'index'])->name('academico.index');
    Route::get('/niveles-educativos', [AcademicController::class, 'page'])->defaults('pageKey', 'niveles-educativos')->name('academico.niveles-educativos');
    Route::get('/modalidad', [AcademicController::class, 'page'])->defaults('pageKey', 'modalidad')->name('academico.modalidad');
    Route::get('/planes-area', [AcademicController::class, 'page'])->defaults('pageKey', 'planes-area')->name('academico.planes-area');
    Route::get('/sistema-evaluacion', [AcademicController::class, 'page'])->defaults('pageKey', 'sistema-evaluacion')->name('academico.sistema-evaluacion');
    Route::get('/proyectos-pedagogicos', [ProjectController::class, 'index'])->name('academico.proyectos-pedagogicos');
    Route::get('/proyectos-pedagogicos/{slug}', [ProjectController::class, 'show'])->name('academico.proyectos-pedagogicos.show');
    Route::get('/calendario-academico', [AcademicController::class, 'page'])->defaults('pageKey', 'calendario-academico')->name('academico.calendario-academico');
    Route::get('/zona-academica', [AcademicController::class, 'page'])->defaults('pageKey', 'zona-academica')->name('academico.zona-academica');
});

Route::prefix('noticias')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('noticias.index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('noticias.show');
});

Route::prefix('transparencia')->group(function () {
    Route::get('/', [TransparencyController::class, 'index'])->name('transparencia.index');
    Route::get('/contratacion', [ContractingController::class, 'index'])->name('transparencia.contratacion.index');
    Route::get('/contratacion/{processCode}', [ContractingController::class, 'show'])->name('transparencia.contratacion.show');
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
    Route::get('/pqrs/consulta', [CitizenAttentionController::class, 'trackPqrs'])->name('atencion.pqrs.track');
    Route::post('/pqrs/consulta', [CitizenAttentionController::class, 'showPqrsStatus'])
        ->middleware('throttle:pqrs')
        ->name('atencion.pqrs.status');
    Route::get('/tramites-servicios', [CitizenAttentionController::class, 'procedures'])->name('atencion.tramites');
    Route::get('/preguntas-frecuentes', [CitizenAttentionController::class, 'faqs'])->name('atencion.faq');
    Route::get('/mapa-sitio', [CitizenAttentionController::class, 'sitemap'])->name('atencion.mapa-sitio');
    Route::get('/participacion', [CitizenAttentionController::class, 'participation'])->name('atencion.participacion');
});
