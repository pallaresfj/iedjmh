<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicIcon
{
    public const DEFAULT_FALLBACK = 'ms:help';

    /**
     * @var array<int, string>
     */
    private const FONT_AWESOME_STYLES = ['solid', 'regular', 'brands'];

    /**
     * @return array{
     *     raw: string,
     *     set: 'ms'|'fa',
     *     style: ?string,
     *     name: string,
     *     normalized: string,
     *     is_valid: bool,
     *     is_legacy: bool,
     *     classes: string
     * }
     */
    public static function metadata(mixed $value, string $fallback = self::DEFAULT_FALLBACK): array
    {
        $raw = is_string($value) ? trim($value) : '';
        $parsed = static::parseKnown($raw);

        if ($parsed === null) {
            $parsed = static::parseKnown($fallback) ?? static::parseKnown(self::DEFAULT_FALLBACK);
        }

        if ($parsed === null) {
            $parsed = [
                'set' => 'ms',
                'style' => null,
                'name' => 'help',
                'normalized' => self::DEFAULT_FALLBACK,
                'is_legacy' => false,
            ];
        }

        $classes = $parsed['set'] === 'ms'
            ? 'material-symbols-outlined'
            : static::fontAwesomeStyleClass((string) $parsed['style']).' fa-'.(string) $parsed['name'];

        return [
            'raw' => $raw,
            'set' => $parsed['set'],
            'style' => $parsed['style'],
            'name' => $parsed['name'],
            'normalized' => $parsed['normalized'],
            'is_valid' => static::parseKnown($raw) !== null,
            'is_legacy' => $parsed['is_legacy'],
            'classes' => $classes,
        ];
    }

    public static function normalize(mixed $value, string $fallback = self::DEFAULT_FALLBACK): string
    {
        return static::metadata($value, $fallback)['normalized'];
    }

    public static function isValidInput(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return static::parseKnown(trim($value)) !== null;
    }

    public static function validationRegex(): string
    {
        return '/^(?:ms:[a-z0-9_]+|fa:(?:solid|regular|brands):[a-z0-9]+(?:-[a-z0-9]+)*|[a-z0-9_]+)$/';
    }

    /**
     * @return array{
     *     set: 'ms'|'fa',
     *     style: ?string,
     *     name: string,
     *     normalized: string,
     *     is_legacy: bool
     * }|null
     */
    private static function parseKnown(string $value): ?array
    {
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, 'ms:')) {
            $name = trim(Str::after($value, 'ms:'));

            if (! preg_match('/^[a-z0-9_]+$/', $name)) {
                return null;
            }

            return [
                'set' => 'ms',
                'style' => null,
                'name' => $name,
                'normalized' => 'ms:'.$name,
                'is_legacy' => false,
            ];
        }

        if (Str::startsWith($value, 'fa:')) {
            $parts = explode(':', $value, 3);

            if (count($parts) !== 3) {
                return null;
            }

            $style = trim((string) ($parts[1] ?? ''));
            $name = trim((string) ($parts[2] ?? ''));

            if (! in_array($style, self::FONT_AWESOME_STYLES, true)) {
                return null;
            }

            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $name)) {
                return null;
            }

            return [
                'set' => 'fa',
                'style' => $style,
                'name' => $name,
                'normalized' => 'fa:'.$style.':'.$name,
                'is_legacy' => false,
            ];
        }

        if (preg_match('/^[a-z0-9_]+$/', $value)) {
            return [
                'set' => 'ms',
                'style' => null,
                'name' => $value,
                'normalized' => 'ms:'.$value,
                'is_legacy' => true,
            ];
        }

        return null;
    }

    private static function fontAwesomeStyleClass(string $style): string
    {
        return match ($style) {
            'regular' => 'fa-regular',
            'brands' => 'fa-brands',
            default => 'fa-solid',
        };
    }
}
