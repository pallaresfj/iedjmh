<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PublicSettings
{
    private const REQUEST_CACHE_KEY = '_public_settings_singleton';

    private const HOME_HERO_IMAGE_FALLBACK_URL = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDMUNlu1vZSYgs1mJ8XI2JBGdEGv7h77-FKsinYr5EjYaApSudFf0jhOBzLc6yoEXKGCF-tewz8MJIFovX4aKHbA0O3FnBStuhctqyV0oVkBdASloF8K2rO8VVM18nBjgTP2zwD60uTY7U6Vw-bB3w4vymqId0y98mtqnopTqtBAvch6WRWhfF7lV9eqtrHoQxcCTLHXNxBGP1xnxW6D-Hw4cLmuICL4qmBewmK1UqmRBf9D7Wau-xa_o7aSt4rGKkayqhXd0Sj8bxl';

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

    public static function homeHeroFallbackImageUrl(): string
    {
        return static::HOME_HERO_IMAGE_FALLBACK_URL;
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
        $primary = static::themeColor('theme_primary');
        $primaryDark = static::themeColor('theme_primary_dark');
        $primaryLight = static::themeColor('theme_primary_light');
        $accent = static::themeColor('theme_accent');
        $success = static::blendHexColors($primary, '#FFFFFF', 0.08);
        $info = static::blendHexColors($primaryLight, '#FFFFFF', 0.05);
        $danger = static::blendHexColors($accent, $primaryDark, 0.42);
        $gray900 = static::themeColor('theme_gray_900');
        $gray700 = static::themeColor('theme_gray_700');
        $gray600 = static::themeColor('theme_gray_600');
        $gray200 = static::themeColor('theme_gray_200');
        $gray100 = static::themeColor('theme_gray_100');

        return [
            '--color-ied-primary' => $primary,
            '--color-ied-primary-dark' => $primaryDark,
            '--color-ied-primary-light' => $primaryLight,
            '--color-ied-accent' => $accent,
            '--color-ied-success' => $success,
            '--color-ied-info' => $info,
            '--color-ied-warning' => $accent,
            '--color-ied-danger' => $danger,
            '--color-ied-gray-900' => $gray900,
            '--color-ied-gray-700' => $gray700,
            '--color-ied-gray-600' => $gray600,
            '--color-ied-gray-200' => $gray200,
            '--color-ied-gray-100' => $gray100,
            '--color-ied-primary-rgb' => static::hexColorToRgbChannels($primary),
            '--color-ied-primary-dark-rgb' => static::hexColorToRgbChannels($primaryDark),
            '--color-ied-primary-light-rgb' => static::hexColorToRgbChannels($primaryLight),
            '--color-ied-accent-rgb' => static::hexColorToRgbChannels($accent),
            '--color-ied-success-rgb' => static::hexColorToRgbChannels($success),
            '--color-ied-info-rgb' => static::hexColorToRgbChannels($info),
            '--color-ied-warning-rgb' => static::hexColorToRgbChannels($accent),
            '--color-ied-danger-rgb' => static::hexColorToRgbChannels($danger),
            '--color-ied-gray-900-rgb' => static::hexColorToRgbChannels($gray900),
            '--color-ied-gray-700-rgb' => static::hexColorToRgbChannels($gray700),
            '--color-ied-gray-600-rgb' => static::hexColorToRgbChannels($gray600),
            '--color-ied-gray-200-rgb' => static::hexColorToRgbChannels($gray200),
            '--color-ied-gray-100-rgb' => static::hexColorToRgbChannels($gray100),
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public static function filamentAdminColors(): array
    {
        $primary = static::themeColor('theme_primary');
        $primaryDark = static::themeColor('theme_primary_dark');
        $primaryLight = static::themeColor('theme_primary_light');
        $accent = static::themeColor('theme_accent');
        $gray900 = static::themeColor('theme_gray_900');
        $gray700 = static::themeColor('theme_gray_700');
        $gray600 = static::themeColor('theme_gray_600');
        $gray200 = static::themeColor('theme_gray_200');
        $gray100 = static::themeColor('theme_gray_100');

        return [
            'primary' => $primary,
            'success' => static::blendHexColors($primary, '#FFFFFF', 0.08),
            'info' => static::blendHexColors($primaryLight, '#FFFFFF', 0.05),
            'warning' => $accent,
            'danger' => static::blendHexColors($accent, $primaryDark, 0.42),
            'gray' => static::filamentAdminGrayPalette($gray100, $gray200, $gray600, $gray700, $gray900),
        ];
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public static function allies(): array
    {
        $allies = static::normalizeAllies(static::get('allies', []));

        if ($allies !== []) {
            return $allies;
        }

        return static::normalizeAllies(config('institution.allies', []));
    }

    /**
     * @return array{address: ?string, phone: ?string, email: ?string, hours: ?string, location: ?string, latitude: ?string, longitude: ?string}
     */
    public static function contact(): array
    {
        return [
            'address' => static::nullableString(static::get('address')),
            'phone' => static::nullableString(static::get('phone')),
            'email' => static::nullableString(static::get('email')),
            'hours' => static::nullableString(static::get('contact_hours')),
            'location' => static::nullableString(static::get('location')),
            'latitude' => static::nullableCoordinate(static::get('location_latitude')),
            'longitude' => static::nullableCoordinate(static::get('location_longitude')),
        ];
    }

    /**
     * @return array{label: string, icon: string}
     */
    public static function academicModality(): array
    {
        $label = static::nullableString(static::get('academic_modality_label', 'Modalidad')) ?? 'Modalidad';
        $icon = static::nullableString(static::get('academic_modality_icon', 'agriculture')) ?? 'agriculture';

        if (! preg_match('/^[a-z0-9_]+$/', $icon)) {
            $icon = 'agriculture';
        }

        return [
            'label' => $label,
            'icon' => $icon,
        ];
    }

    public static function logoFallbackIcon(): string
    {
        return static::academicModality()['icon'];
    }

    /**
     * @return array{
     *     flag_intro: string,
     *     flag_stripes: array<int, array{name: string, description: string, color_hex: string}>,
     *     shield_intro: string,
     *     shield_image_url: ?string,
     *     shield_items: array<int, array{title: string, description: string, icon: string}>,
     *     hymn_title: string,
     *     hymn_audio_url: ?string,
     *     hymn_lyrics: string
     * }
     */
    public static function symbols(): array
    {
        $flagStripes = static::normalizeSymbolStripes(static::get('symbols_flag_stripes', []));
        $shieldItems = static::normalizeSymbolShieldItems(static::get('symbols_shield_items', []));

        return [
            'flag_intro' => (string) static::get(
                'symbols_flag_intro',
                'Nuestra bandera expresa la esperanza y el trabajo. Sus franjas representan biodiversidad, riqueza academica y transparencia.',
            ),
            'flag_stripes' => $flagStripes !== [] ? $flagStripes : [
                [
                    'name' => 'Verde Bosque',
                    'description' => 'Representa la biodiversidad regional y el compromiso con el desarrollo agropecuario sostenible.',
                    'color_hex' => '#2E7D32',
                ],
                [
                    'name' => 'Franja Amarilla',
                    'description' => 'Simboliza la riqueza intelectual de nuestros estudiantes y la prosperidad de nuestras cosechas.',
                    'color_hex' => '#FACC15',
                ],
                [
                    'name' => 'Blanco Puro',
                    'description' => 'Evoca la transparencia, la paz y los valores eticos de nuestra comunidad educativa.',
                    'color_hex' => '#FFFFFF',
                ],
            ],
            'shield_intro' => (string) static::get(
                'symbols_shield_intro',
                'Nuestro escudo resume la vocacion institucional en ciencia, campo y futuro, integrando formacion tecnica, academica y humana.',
            ),
            'shield_image_url' => static::mediaUrl(static::nullableString(static::get('symbols_shield_image_path'))),
            'shield_items' => $shieldItems !== [] ? $shieldItems : [
                [
                    'title' => 'Ganado y Campo',
                    'description' => 'Representa la vocacion pecuaria de Pivijay y el componente tecnico de nuestra formacion.',
                    'icon' => 'agriculture',
                ],
                [
                    'title' => 'Libro Abierto',
                    'description' => 'Simboliza el conocimiento academico y la busqueda permanente de la verdad.',
                    'icon' => 'menu_book',
                ],
                [
                    'title' => 'Naturaleza Viva',
                    'description' => 'Integra el compromiso ambiental y el respeto por el territorio como base de nuestra identidad.',
                    'icon' => 'grass',
                ],
            ],
            'hymn_title' => (string) static::get('symbols_hymn_title', 'Himno Institucional'),
            'hymn_audio_url' => static::mediaUrl(static::nullableString(static::get('symbols_hymn_audio_path'))),
            'hymn_lyrics' => static::normalizeHymnLyrics(
                static::nullableString(static::get('symbols_hymn_lyrics'))
            ),
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

    public static function clearCache(): void
    {
        Cache::forget('public_settings_singleton');
        static::$resolved = false;
        static::$setting = null;
    }

    private static function query(): ?Setting
    {
        try {
            if (! Schema::hasTable('settings')) {
                return null;
            }

            /** @var array<string, mixed>|null $attributes */
            $attributes = Cache::remember('public_settings_singleton', 300, function (): ?array {
                $setting = Setting::query()
                    ->where('singleton', 1)
                    ->first();

                return $setting?->getAttributes();
            });

            if (! is_array($attributes) || $attributes === []) {
                return null;
            }

            $setting = new Setting;
            $setting->setRawAttributes($attributes, true);

            return $setting;
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
            'academic_modality_label' => 'Modalidad',
            'academic_modality_icon' => 'agriculture',
            'location' => collect([config('institution.city'), config('institution.department')])->filter()->join(', '),
            'location_latitude' => null,
            'location_longitude' => null,
            'address' => config('institution.address'),
            'phone' => config('institution.phone'),
            'email' => config('institution.email'),
            'contact_hours' => null,
            'siee' => config('institution.siee'),
            'aula_virtual' => config('institution.aula_virtual'),
            'logo_path' => config('institution.logo'),
            'home_hero_cta_target' => '_self',
            'symbols_flag_intro' => 'Nuestra bandera expresa la esperanza y el trabajo. Sus franjas representan biodiversidad, riqueza academica y transparencia.',
            'symbols_flag_stripes' => [],
            'symbols_shield_intro' => 'Nuestro escudo resume la vocacion institucional en ciencia, campo y futuro, integrando formacion tecnica, academica y humana.',
            'symbols_shield_image_path' => null,
            'symbols_shield_items' => [],
            'symbols_hymn_title' => 'Himno Institucional',
            'symbols_hymn_audio_path' => null,
            'symbols_hymn_lyrics' => "Coro\nOh glorioso claustro Herrera,\nfaro de luz y de saber,\nen tus campos se forja el futuro,\ndel hombre que quiere crecer.\n\nEstrofa I\nBajo el cielo de nuestra llanura,\ndonde el verde se funde con paz,\nestudiamos con fe y con cordura,\npara el agro en su luz eficaz.\n\nEstrofa II\nCon las manos labramos la tierra,\ncon el alma buscamos la verdad,\nJose Maria Herrera nos orienta,\na la ciencia y a la libertad.\n\nEstrofa III\nPivijay nos entrega su cuna,\nla institucion nos da la vision,\nno habra nunca mayor fortuna,\nque esta noble educacion.",
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

    private static function hexColorToRgbChannels(string $value): string
    {
        $channels = static::hexToRgbChannels($value);

        if ($channels === null) {
            return '0, 0, 0';
        }

        return implode(', ', array_map(static fn (int $channel): string => (string) $channel, $channels));
    }

    /**
     * @return array<int, string>
     */
    private static function filamentAdminGrayPalette(
        string $gray100,
        string $gray200,
        string $gray600,
        string $gray700,
        string $gray900,
    ): array {
        return [
            50 => static::blendHexColors($gray100, '#FFFFFF', 0.65),
            100 => $gray100,
            200 => $gray200,
            300 => static::blendHexColors($gray200, $gray600, 0.18),
            400 => static::blendHexColors($gray200, $gray600, 0.42),
            500 => static::blendHexColors($gray200, $gray600, 0.72),
            600 => $gray600,
            700 => $gray700,
            800 => static::blendHexColors($gray700, $gray900, 0.5),
            900 => $gray900,
            950 => static::blendHexColors($gray900, '#000000', 0.36),
        ];
    }

    private static function blendHexColors(string $baseColor, string $targetColor, float $targetWeight): string
    {
        $baseChannels = static::hexToRgbChannels($baseColor) ?? [0, 0, 0];
        $targetChannels = static::hexToRgbChannels($targetColor) ?? [0, 0, 0];

        $targetWeight = max(0, min(1, $targetWeight));
        $baseWeight = 1 - $targetWeight;

        return sprintf(
            '#%02X%02X%02X',
            (int) round(($baseChannels[0] * $baseWeight) + ($targetChannels[0] * $targetWeight)),
            (int) round(($baseChannels[1] * $baseWeight) + ($targetChannels[1] * $targetWeight)),
            (int) round(($baseChannels[2] * $baseWeight) + ($targetChannels[2] * $targetWeight)),
        );
    }

    /**
     * @return array{int, int, int}|null
     */
    private static function hexToRgbChannels(string $value): ?array
    {
        $sanitized = static::sanitizeHexColor($value);

        if ($sanitized === null) {
            return null;
        }

        return [
            hexdec(substr($sanitized, 1, 2)),
            hexdec(substr($sanitized, 3, 2)),
            hexdec(substr($sanitized, 5, 2)),
        ];
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private static function normalizeAllies(mixed $allies): array
    {
        if (! is_array($allies)) {
            return [];
        }

        $normalized = [];

        foreach ($allies as $ally) {
            if (! is_array($ally)) {
                continue;
            }

            $name = trim((string) ($ally['name'] ?? $ally['label'] ?? ''));
            $url = trim((string) ($ally['url'] ?? ''));

            if ($name === '' || ! static::isValidAllyUrl($url)) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'url' => $url,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<int, array{name: string, description: string, color_hex: string}>
     */
    private static function normalizeSymbolStripes(mixed $stripes): array
    {
        if (! is_array($stripes)) {
            return [];
        }

        $normalized = [];

        foreach ($stripes as $stripe) {
            if (! is_array($stripe)) {
                continue;
            }

            $name = trim((string) ($stripe['name'] ?? ''));
            $description = trim((string) ($stripe['description'] ?? ''));
            $color = static::sanitizeHexColor((string) ($stripe['color_hex'] ?? ''));

            if ($name === '' || $description === '' || $color === null) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'description' => $description,
                'color_hex' => $color,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<int, array{title: string, description: string, icon: string}>
     */
    private static function normalizeSymbolShieldItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));
            $icon = trim((string) ($item['icon'] ?? 'shield'));

            if ($title === '' || $description === '') {
                continue;
            }

            if (! preg_match('/^[a-z0-9_]+$/', $icon)) {
                $icon = 'shield';
            }

            $normalized[] = [
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
            ];
        }

        return $normalized;
    }

    private static function normalizeHymnLyrics(?string $value): string
    {
        $content = trim((string) ($value ?? ''));

        if ($content === '') {
            $content = (string) static::fallbackFor('symbols_hymn_lyrics');
        }

        if (preg_match('/<[^>]+>/', $content) === 1) {
            $content = preg_replace('/<\s*br\s*\/?>/i', "\n", $content) ?? $content;
            $content = preg_replace('/<\/\s*(p|div|h[1-6]|li|blockquote|tr|table|ul|ol)\s*>/i', "\n", $content) ?? $content;
            $content = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace("/[ \t]+\n/", "\n", $content) ?? $content;
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return trim($content);
    }

    private static function isValidAllyUrl(string $url): bool
    {
        if ($url === '#') {
            return true;
        }

        if (Str::startsWith($url, '/')) {
            return true;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true);
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private static function nullableCoordinate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $normalized = number_format((float) $value, 7, '.', '');

        return rtrim(rtrim($normalized, '0'), '.');
    }
}
