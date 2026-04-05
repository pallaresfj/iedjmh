<?php

use App\Http\Controllers\Admin\GraduateDocumentFileController;
use App\Http\Controllers\Admin\MatriculaRequestAttachmentController;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/public.php';

Route::middleware('guest')->prefix('auth/google')->name('auth.google.')->group(function (): void {
    Route::get('/redirect', [GoogleAuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [GoogleAuthController::class, 'callback'])->name('callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/admin/matricula-requests/{matriculaRequest}/attachments/{attachmentIndex}', MatriculaRequestAttachmentController::class)
        ->whereNumber('attachmentIndex')
        ->name('admin.matricula-requests.attachments.show');

    Route::get('/admin/graduates/documents/{document}/file', GraduateDocumentFileController::class)
        ->name('admin.graduates.documents.file.show');
});

require __DIR__.'/settings.php';
