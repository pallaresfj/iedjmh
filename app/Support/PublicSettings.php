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
                return Storage::disk('public')->url($normalizedPath);
            }
        } catch (Throwable) {
            // Fallback to conventional public storage path.
        }

        return '/storage/'.$normalizedPath;
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
            default => null,
        };
    }
}
