<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PublicSettings
{
    private const REQUEST_CACHE_KEY = '_public_settings_singleton';

    /**
     * @var array<string, string>
     */
    private const THEME_COLOR_DEFAULTS = [
        'theme_primary' => '#2E7D32',
        'theme_primary_dark' => '#1B5E20',
        'theme_primary_light' => '#66BB6A',
        'theme_accent' => '#F57C00',
        'theme_gray_900' => '#263238',
        'theme_gray_700' => '#4C5A61',
        'theme_gray_600' => '#5F6F77',
        'theme_gray_200' => '#DFE5E8',
        'theme_gray_100' => '#F5F7FA',
    ];

    private static bool $resolved = false;

    private static ?Setting $setting = null;

    public static function get(string $key, mixed $fallback = null): mixed
    {
        if ($fallback === null) {
            $fallback = static::fallbackFor($key);
        }

        $setting = static::resolve();

        if (! $setting) {
            return $fallback;
        }

        $value = $setting->getAttribute($key);

        if (is_string($value)) {
            $value = trim($value);
        }

        return filled($value) ? $value : $fallback;
    }

    public static function mediaUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $path = trim((string) $path);

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        try {
            if (Storage::disk('public')->exists($normalizedPath)) {
                return static::normalizePublicDiskUrl((string) Storage::disk('public')->url($normalizedPath), $normalizedPath);
            }
        } catch (Throwable) {
            // Fallback to conventional public storage path.
        }

        return '/storage/'.$normalizedPath;
    }

    private static function normalizePublicDiskUrl(string $url, string $normalizedPath): string
    {
        if ($url === '') {
            return '/storage/'.$normalizedPath;
        }

        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return '/storage/'.$normalizedPath;
        }

        $urlHost = $parts['host'] ?? null;
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $requestHost = app()->bound('request') ? request()->getHost() : null;
        $knownHosts = array_filter([
            is_string($appHost) ? $appHost : null,
            $requestHost,
        ]);

        if (is_string($urlHost) && in_array($urlHost, $knownHosts, true)) {
            $path = $parts['path'] ?? '/storage/'.$normalizedPath;
            $query = isset($parts['query']) ? '?'.$parts['query'] : '';

            return $path.$query;
        }

        if (app()->bound('request') && request()->isSecure() && Str::startsWith($url, 'http://')) {
            return Str::replaceFirst('http://', 'https://', $url);
        }

        return $url;
    }

    /**
     * @return array<string, string>
     */
    public static function themeColors(): array
    {
        return [
            '--color-ied-primary' => static::themeColor('theme_primary'),
            '--color-ied-primary-dark' => static::themeColor('theme_primary_dark'),
            '--color-ied-primary-light' => static::themeColor('theme_primary_light'),
            '--color-ied-accent' => static::themeColor('theme_accent'),
            '--color-ied-gray-900' => static::themeColor('theme_gray_900'),
            '--color-ied-gray-700' => static::themeColor('theme_gray_700'),
            '--color-ied-gray-600' => static::themeColor('theme_gray_600'),
            '--color-ied-gray-200' => static::themeColor('theme_gray_200'),
            '--color-ied-gray-100' => static::themeColor('theme_gray_100'),
        ];
    }

    private static function resolve(): ?Setting
    {
        if (app()->bound('request')) {
            $request = request();

            if ($request->attributes->has(static::REQUEST_CACHE_KEY)) {
                $cached = $request->attributes->get(static::REQUEST_CACHE_KEY);

                return $cached instanceof Setting ? $cached : null;
            }

            $setting = static::query();
            $request->attributes->set(static::REQUEST_CACHE_KEY, $setting ?: false);

            return $setting;
        }

        if (! static::$resolved) {
            static::$setting = static::query();
            static::$resolved = true;
        }

        return static::$setting;
    }

    private static function query(): ?Setting
    {
        try {
            if (! Schema::hasTable('settings')) {
                return null;
            }

            return Setting::query()
                ->where('singleton', 1)
                ->first();
        } catch (Throwable) {
            return null;
        }
    }

    private static function fallbackFor(string $key): mixed
    {
        return match ($key) {
            'institution_name' => config('institution.display_name', config('institution.name', 'IED JOSÉ MARÍA HERRERA')),
            'dane' => config('institution.dane'),
            'nit' => config('institution.nit'),
            'location' => collect([config('institution.city'), config('institution.department')])->filter()->join(', '),
            'siee' => config('institution.siee'),
            'aula_virtual' => config('institution.aula_virtual'),
            'logo_path' => config('institution.logo'),
            'home_hero_cta_target' => '_self',
            'theme_primary' => static::THEME_COLOR_DEFAULTS['theme_primary'],
            'theme_primary_dark' => static::THEME_COLOR_DEFAULTS['theme_primary_dark'],
            'theme_primary_light' => static::THEME_COLOR_DEFAULTS['theme_primary_light'],
            'theme_accent' => static::THEME_COLOR_DEFAULTS['theme_accent'],
            'theme_gray_900' => static::THEME_COLOR_DEFAULTS['theme_gray_900'],
            'theme_gray_700' => static::THEME_COLOR_DEFAULTS['theme_gray_700'],
            'theme_gray_600' => static::THEME_COLOR_DEFAULTS['theme_gray_600'],
            'theme_gray_200' => static::THEME_COLOR_DEFAULTS['theme_gray_200'],
            'theme_gray_100' => static::THEME_COLOR_DEFAULTS['theme_gray_100'],
            default => null,
        };
    }

    private static function themeColor(string $key): string
    {
        $fallback = static::THEME_COLOR_DEFAULTS[$key];
        $value = static::get($key, $fallback);

        if (! is_string($value)) {
            return $fallback;
        }

        $sanitized = static::sanitizeHexColor($value);

        return $sanitized ?? $fallback;
    }

    private static function sanitizeHexColor(string $value): ?string
    {
        $value = strtoupper(trim($value));

        if (! preg_match('/^#[0-9A-F]{6}$/', $value)) {
            return null;
        }

        return $value;
    }
}
