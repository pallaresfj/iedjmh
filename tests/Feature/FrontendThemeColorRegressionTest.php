<?php

use Illuminate\Support\Facades\File;

test('public dark theme css avoids legacy hardcoded accent literals', function () {
    $css = File::get(resource_path('css/public.css'));

    expect($css)
        ->not->toContain('#91f78e')
        ->not->toContain('#52b555')
        ->not->toContain('#a7ffa4')
        ->not->toContain('#66c96a')
        ->not->toContain('rgba(145, 247, 142');
});

test('frontend views avoid emerald and green utility classes for messaging states', function () {
    $targets = [
        resource_path('views/public'),
        resource_path('views/components/public'),
        resource_path('views/pages/auth'),
        resource_path('views/pages/settings'),
        resource_path('views/components/auth-session-status.blade.php'),
    ];

    $files = collect($targets)
        ->flatMap(function (string $target): array {
            if (is_dir($target)) {
                return array_map(
                    static fn (\SplFileInfo $file): string => $file->getPathname(),
                    File::allFiles($target),
                );
            }

            return [$target];
        })
        ->all();

    $content = collect($files)
        ->map(static fn (string $file): string => File::get($file))
        ->implode("\n");

    expect($content)
        ->not->toMatch('/\b(?:text|bg|border)-(?:emerald|green)-\d{2,3}\b/')
        ->not->toContain('dark:text-green')
        ->not->toContain('dark:border-green')
        ->not->toContain('dark:bg-green');
});

test('matricula styles avoid hardcoded legacy colors and amber utility alerts', function () {
    $css = File::get(resource_path('css/public.css'));
    $matriculaView = File::get(resource_path('views/public/matricula/index.blade.php'));

    expect($css)
        ->not->toContain('#f1f3f2')
        ->not->toContain('#174f35')
        ->not->toContain('#5f6f67')
        ->not->toContain('#d9e0dc')
        ->not->toContain('#172824')
        ->not->toContain('#dce3df')
        ->not->toContain('#d7dfdb')
        ->not->toContain('#f7f9f8')
        ->not->toContain('#97a69f')
        ->not->toContain('#2a3f35')
        ->not->toContain('#60716a')
        ->not->toContain('#1f5e28')
        ->not->toContain('#b42318')
        ->not->toContain('#226027')
        ->not->toContain('#667771');

    expect($matriculaView)
        ->toContain('public-alert--warning')
        ->not->toContain('border-amber-')
        ->not->toContain('bg-amber-')
        ->not->toContain('text-amber-');
});
