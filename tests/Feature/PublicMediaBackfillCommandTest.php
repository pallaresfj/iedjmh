<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

test('media backfill copies configured public directories from local disk', function () {
    Storage::fake('local');
    Storage::fake('public');

    Storage::disk('local')->put('projects/proyecto.jpg', 'contenido proyecto');
    Storage::disk('local')->put('projects/gallery/proyecto-galeria.jpg', 'contenido galeria');
    Storage::disk('local')->put('posts/noticia.jpg', 'contenido noticia');
    Storage::disk('local')->put('staff-members/directivo.jpg', 'contenido staff');
    Storage::disk('local')->put('documents/no-migrar.pdf', 'no migrar');

    $exitCode = Artisan::call('media:backfill-public-uploads');

    expect($exitCode)->toBe(0);

    Storage::disk('public')->assertExists('projects/proyecto.jpg');
    Storage::disk('public')->assertExists('projects/gallery/proyecto-galeria.jpg');
    Storage::disk('public')->assertExists('posts/noticia.jpg');
    Storage::disk('public')->assertExists('staff-members/directivo.jpg');
    Storage::disk('public')->assertMissing('documents/no-migrar.pdf');
});

test('media backfill dry run reports pending files without copying', function () {
    Storage::fake('local');
    Storage::fake('public');

    Storage::disk('local')->put('projects/proyecto.jpg', 'contenido proyecto');

    $exitCode = Artisan::call('media:backfill-public-uploads', ['--dry-run' => true]);

    expect($exitCode)->toBe(0);
    Storage::disk('public')->assertMissing('projects/proyecto.jpg');
    expect(Artisan::output())->toContain('Planificados (dry-run): 1');
});
