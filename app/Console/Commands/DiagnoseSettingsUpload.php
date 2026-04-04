<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DiagnoseSettingsUpload extends Command
{
    protected $signature = 'diagnose:settings-upload {--json : Print report as JSON}';

    protected $description = 'Diagnose Livewire/Filament upload conditions for the Settings page.';

    public function handle(): int
    {
        $report = [
            'timestamp' => now()->toIso8601String(),
            'environment' => $this->environmentSection(),
            'uploads' => $this->uploadSection(),
            'session' => $this->sessionSection(),
            'filesystem' => $this->filesystemSection(),
            'database' => $this->databaseSection(),
            'livewire_routes' => $this->livewireRoutesSection(),
            'livewire_http_status' => $this->livewireHttpStatusSection(),
        ];

        $findings = $this->buildFindings($report);
        $report['findings'] = $findings;

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $this->hasCriticalFindings($findings) ? self::FAILURE : self::SUCCESS;
        }

        $this->components->info('Settings Upload Diagnostic Report');
        $this->newLine();

        $this->renderSection('Environment', $report['environment']);
        $this->renderSection('Uploads', $report['uploads']);
        $this->renderSection('Session', $report['session']);
        $this->renderSection('Filesystem', $report['filesystem']);
        $this->renderSection('Database', $report['database']);
        $this->renderSection('Livewire HTTP status', $report['livewire_http_status']);

        $this->line('Livewire routes:');
        if ($report['livewire_routes'] === []) {
            $this->line('  - none');
        } else {
            foreach ($report['livewire_routes'] as $route) {
                $this->line("  - [{$route['method']}] {$route['uri']} (name: {$route['name']})");
            }
        }

        $this->newLine();
        $this->line('Findings:');
        if ($findings === []) {
            $this->components->info('  No findings. The runtime checks look consistent.');
        } else {
            foreach ($findings as $finding) {
                $this->line(sprintf(
                    '  - [%s] %s',
                    strtoupper((string) $finding['severity']),
                    (string) $finding['message'],
                ));
            }
        }

        $this->newLine();
        $this->line('Suggested next checks (manual, in production):');
        $this->line('  1) DevTools Network -> inspect POST /livewire-*/upload-file and /livewire-*/update.');
        $this->line('  2) Tail logs while reproducing:');
        $this->line('     - tail -f /var/www/html/storage/logs/laravel.log');
        $this->line('     - tail -f /var/log/nginx/access.log');
        $this->line('     - tail -f /var/log/nginx/error.log');

        return $this->hasCriticalFindings($findings) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function environmentSection(): array
    {
        return [
            'app_env' => app()->environment(),
            'app_url' => Config::get('app.url'),
            'app_debug' => (bool) Config::get('app.debug'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadSection(): array
    {
        $uploadMax = (string) ini_get('upload_max_filesize');
        $postMax = (string) ini_get('post_max_size');
        $memoryLimit = (string) ini_get('memory_limit');
        $maxExecution = (string) ini_get('max_execution_time');

        return [
            'upload_max_filesize' => $uploadMax,
            'post_max_size' => $postMax,
            'memory_limit' => $memoryLimit,
            'max_execution_time' => $maxExecution,
            'upload_max_filesize_bytes' => $this->toBytes($uploadMax),
            'post_max_size_bytes' => $this->toBytes($postMax),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sessionSection(): array
    {
        return [
            'driver' => Config::get('session.driver'),
            'table' => Config::get('session.table'),
            'domain' => Config::get('session.domain'),
            'secure' => Config::get('session.secure'),
            'same_site' => Config::get('session.same_site'),
            'encrypt' => Config::get('session.encrypt'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filesystemSection(): array
    {
        $publicDiskRoot = (string) Config::get('filesystems.disks.public.root');
        $publicDiskUrl = (string) Config::get('filesystems.disks.public.url');
        $defaultDisk = (string) Config::get('filesystems.default');

        $publicDiskPath = null;
        $diskPathError = null;

        try {
            $publicDiskPath = Storage::disk('public')->path('');
        } catch (Throwable $exception) {
            $diskPathError = $exception->getMessage();
        }

        $storageLink = public_path('storage');
        $settingsDir = rtrim($publicDiskRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'settings';

        return [
            'default_disk' => $defaultDisk,
            'public_disk_root' => $publicDiskRoot,
            'public_disk_url' => $publicDiskUrl,
            'public_disk_path' => $publicDiskPath,
            'public_disk_path_error' => $diskPathError,
            'public_root_exists' => is_dir($publicDiskRoot),
            'public_root_writable' => is_writable($publicDiskRoot),
            'settings_dir_exists' => is_dir($settingsDir),
            'settings_dir_writable' => is_writable($settingsDir),
            'public_storage_link_path' => $storageLink,
            'public_storage_link_exists' => file_exists($storageLink),
            'public_storage_link_is_symlink' => is_link($storageLink),
            'public_storage_link_target' => is_link($storageLink) ? readlink($storageLink) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseSection(): array
    {
        $connectionOk = true;
        $connectionError = null;

        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            $connectionOk = false;
            $connectionError = $exception->getMessage();
        }

        $hasSettingsTable = false;
        $settingsCount = null;
        $settingsError = null;
        $hasSessionsTable = false;

        if ($connectionOk) {
            try {
                $hasSettingsTable = Schema::hasTable('settings');
                $hasSessionsTable = Schema::hasTable((string) Config::get('session.table', 'sessions'));

                if ($hasSettingsTable) {
                    $settingsCount = Setting::query()->count();
                }
            } catch (Throwable $exception) {
                $settingsError = $exception->getMessage();
            }
        }

        return [
            'connection_ok' => $connectionOk,
            'connection_error' => $connectionError,
            'has_settings_table' => $hasSettingsTable,
            'settings_rows' => $settingsCount,
            'has_sessions_table' => $hasSessionsTable,
            'settings_check_error' => $settingsError,
        ];
    }

    /**
     * @return array<int, array{method: string, uri: string, name: string}>
     */
    private function livewireRoutesSection(): array
    {
        return collect(Route::getRoutes())
            ->map(fn ($route): array => [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName() ?? '-',
            ])
            ->filter(function (array $route): bool {
                $uri = Str::lower((string) $route['uri']);
                $name = Str::lower((string) $route['name']);

                return Str::contains($uri, 'livewire') || Str::contains($name, 'livewire');
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function livewireHttpStatusSection(): array
    {
        $accessLogPath = '/var/log/nginx/access.log';
        $maxLines = 1200;

        $section = [
            'access_log_path' => $accessLogPath,
            'access_log_readable' => is_readable($accessLogPath),
            'lines_scanned' => 0,
            'matched_livewire_requests' => 0,
            'update_requests' => 0,
            'upload_requests' => 0,
            'other_livewire_requests' => 0,
            'status_counts' => [],
            'non_2xx_count' => 0,
            'sample_non_2xx' => [],
        ];

        if (! $section['access_log_readable']) {
            return $section;
        }

        try {
            $lines = file($accessLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        } catch (Throwable $exception) {
            $section['access_log_readable'] = false;
            $section['access_log_error'] = $exception->getMessage();

            return $section;
        }

        if (! is_array($lines) || $lines === []) {
            return $section;
        }

        $tailLines = array_slice($lines, -$maxLines);
        $section['lines_scanned'] = count($tailLines);
        $statusCounts = [];
        $non2xxSamples = [];

        foreach ($tailLines as $line) {
            if (! preg_match('/"(?P<method>[A-Z]+)\s+(?P<uri>\S+)\s+HTTP\/[0-9.]+"\s+(?P<status>[0-9]{3})\s+/', $line, $matches)) {
                continue;
            }

            $uriRaw = (string) ($matches['uri'] ?? '');
            $uriPath = (string) (parse_url($uriRaw, PHP_URL_PATH) ?? $uriRaw);

            if (! Str::contains($uriPath, '/livewire-')) {
                continue;
            }

            $section['matched_livewire_requests']++;

            if (Str::endsWith($uriPath, '/update')) {
                $section['update_requests']++;
            } elseif (Str::endsWith($uriPath, '/upload-file')) {
                $section['upload_requests']++;
            } else {
                $section['other_livewire_requests']++;
            }

            $status = (string) ($matches['status'] ?? '');
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

            if (! Str::startsWith($status, '2')) {
                $section['non_2xx_count']++;

                if (count($non2xxSamples) < 12) {
                    $non2xxSamples[] = sprintf('%s %s', $status, $uriPath);
                }
            }
        }

        ksort($statusCounts);

        $section['status_counts'] = $statusCounts;
        $section['sample_non_2xx'] = $non2xxSamples;

        return $section;
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<int, array{severity: string, message: string}>
     */
    private function buildFindings(array $report): array
    {
        $findings = [];

        if (! (bool) data_get($report, 'database.connection_ok')) {
            $findings[] = [
                'severity' => 'critical',
                'message' => 'No hay conexión a la base de datos.',
            ];
        }

        if ((bool) data_get($report, 'database.connection_ok')
            && (string) data_get($report, 'session.driver') === 'database'
            && ! (bool) data_get($report, 'database.has_sessions_table')
        ) {
            $findings[] = [
                'severity' => 'critical',
                'message' => 'SESSION_DRIVER=database pero la tabla de sesiones no existe.',
            ];
        }

        if (! (bool) data_get($report, 'filesystem.public_root_exists')) {
            $findings[] = [
                'severity' => 'critical',
                'message' => 'No existe el root del disco public.',
            ];
        } elseif (! (bool) data_get($report, 'filesystem.public_root_writable')) {
            $findings[] = [
                'severity' => 'critical',
                'message' => 'El root del disco public no tiene permisos de escritura.',
            ];
        }

        if ((string) data_get($report, 'environment.app_url') !== ''
            && Str::startsWith((string) data_get($report, 'environment.app_url'), 'https://')
            && data_get($report, 'session.secure') === false
        ) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'APP_URL es HTTPS pero SESSION_SECURE_COOKIE está en false.',
            ];
        }

        if ((int) data_get($report, 'uploads.post_max_size_bytes', 0) > 0
            && (int) data_get($report, 'uploads.upload_max_filesize_bytes', 0) > 0
            && (int) data_get($report, 'uploads.post_max_size_bytes', 0) < (int) data_get($report, 'uploads.upload_max_filesize_bytes', 0)
        ) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'post_max_size es menor que upload_max_filesize.',
            ];
        }

        if ((int) data_get($report, 'uploads.upload_max_filesize_bytes', 0) > 0
            && (int) data_get($report, 'uploads.upload_max_filesize_bytes', 0) < (2 * 1024 * 1024)
        ) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'upload_max_filesize es menor a 2MB.',
            ];
        }

        if ((int) data_get($report, 'uploads.post_max_size_bytes', 0) > 0
            && (int) data_get($report, 'uploads.post_max_size_bytes', 0) < (2 * 1024 * 1024)
        ) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'post_max_size es menor a 2MB.',
            ];
        }

        $livewireRoutes = collect((array) data_get($report, 'livewire_routes', []));
        $hasUploadRoute = $livewireRoutes->contains(fn (array $route): bool => Str::endsWith((string) ($route['uri'] ?? ''), '/upload-file')
            || Str::contains((string) ($route['name'] ?? ''), 'upload-file'));
        $hasUpdateRoute = $livewireRoutes->contains(fn (array $route): bool => Str::endsWith((string) ($route['uri'] ?? ''), '/update')
            || Str::contains((string) ($route['name'] ?? ''), 'livewire.update')
            || Str::contains((string) ($route['name'] ?? ''), 'default-livewire.update'));

        if (! $hasUploadRoute || ! $hasUpdateRoute) {
            $findings[] = [
                'severity' => 'critical',
                'message' => 'No se detectaron correctamente las rutas de Livewire upload/update en runtime.',
            ];
        }

        $livewireStatus = (array) data_get($report, 'livewire_http_status', []);
        $statusCounts = (array) ($livewireStatus['status_counts'] ?? []);

        if ((bool) ($livewireStatus['access_log_readable'] ?? false) === false) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'No se pudo leer /var/log/nginx/access.log para auditar estados HTTP de Livewire.',
            ];
        } elseif ((int) ($livewireStatus['matched_livewire_requests'] ?? 0) === 0) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'No se encontraron requests Livewire recientes en access.log. Reproduce un guardado y vuelve a ejecutar el diagnóstico.',
            ];
        }

        if ((int) ($statusCounts['419'] ?? 0) > 0) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'Se detectaron respuestas 419 en requests Livewire recientes (sesión/cookies/CSRF).',
            ];
        }

        if ((int) ($statusCounts['422'] ?? 0) > 0) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'Se detectaron respuestas 422 en requests Livewire recientes (validación de formulario).',
            ];
        }

        $serverErrorCount = collect($statusCounts)
            ->filter(fn (int $count, string $status): bool => (int) $status >= 500)
            ->sum();

        if ($serverErrorCount > 0) {
            $findings[] = [
                'severity' => 'critical',
                'message' => 'Se detectaron respuestas 5xx en requests Livewire recientes (backend/proxy inestable).',
            ];
        }

        if (! (bool) data_get($report, 'filesystem.public_storage_link_is_symlink')) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'public/storage no es un symlink válido. Puede romper previews de archivos.',
            ];
        }

        if ((bool) data_get($report, 'database.has_settings_table') && (int) data_get($report, 'database.settings_rows', 0) === 0) {
            $findings[] = [
                'severity' => 'warning',
                'message' => 'La tabla settings existe pero no tiene registros.',
            ];
        }

        return $findings;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function renderSection(string $title, array $values): void
    {
        $this->line($title.':');

        foreach ($values as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = 'null';
            } elseif (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES);
            }

            $this->line("  - {$key}: {$value}");
        }

        $this->newLine();
    }

    private function hasCriticalFindings(array $findings): bool
    {
        return collect($findings)->contains(fn (array $finding): bool => ($finding['severity'] ?? null) === 'critical');
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (float) $value;
        $unit = strtoupper(substr($value, -1));

        return match ($unit) {
            'G' => (int) round($number * 1024 * 1024 * 1024),
            'M' => (int) round($number * 1024 * 1024),
            'K' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }
}
