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
