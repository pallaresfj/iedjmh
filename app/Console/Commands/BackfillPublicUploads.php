<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackfillPublicUploads extends Command
{
    /**
     * @var array<int, string>
     */
    private const DIRECTORIES = [
        'projects',
        'projects/gallery',
        'posts',
        'staff-members',
    ];

    protected $signature = 'media:backfill-public-uploads
        {--dry-run : Simula la migracion sin copiar archivos}';

    protected $description = 'Backfill de uploads publicos desde disco local/private hacia disco public.';

    public function handle(): int
    {
        $localDisk = Storage::disk('local');
        $publicDisk = Storage::disk('public');
        $isDryRun = (bool) $this->option('dry-run');

        $copied = 0;
        $planned = 0;
        $skipped = 0;
        $failed = 0;

        $this->components->info($isDryRun
            ? 'Ejecutando simulacion de migracion de uploads publicos...'
            : 'Ejecutando migracion de uploads publicos...');

        foreach (self::DIRECTORIES as $directory) {
            $this->line(" - Escaneando: {$directory}");

            try {
                $files = $localDisk->allFiles($directory);
            } catch (Throwable $exception) {
                $failed++;
                $this->error("   Error al listar {$directory}: {$exception->getMessage()}");

                continue;
            }

            if ($files === []) {
                $this->line('   Sin archivos para procesar.');

                continue;
            }

            foreach ($files as $sourcePath) {
                $targetPath = ltrim($sourcePath, '/');

                if ($publicDisk->exists($targetPath)) {
                    $skipped++;

                    continue;
                }

                if ($isDryRun) {
                    $planned++;

                    continue;
                }

                try {
                    $publicDisk->put($targetPath, $localDisk->get($sourcePath));
                    $copied++;
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error("   Error al copiar {$sourcePath}: {$exception->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->line('Resumen:');
        $this->line(' - Dry run: '.($isDryRun ? 'si' : 'no'));
        $this->line(" - Copiados: {$copied}");
        $this->line(" - Planificados (dry-run): {$planned}");
        $this->line(" - Omitidos (ya existian): {$skipped}");
        $this->line(" - Errores: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
