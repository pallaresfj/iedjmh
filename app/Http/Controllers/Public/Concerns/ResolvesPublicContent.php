<?php

namespace App\Http\Controllers\Public\Concerns;

use App\Models\Page;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

trait ResolvesPublicContent
{
    /**
     * @var array<string, bool>
     */
    private array $knownTablePresence = [];

    /**
     * @var array<string, bool>
     */
    private array $knownColumnPresence = [];

    protected function canQueryTable(string $table): bool
    {
        if (array_key_exists($table, $this->knownTablePresence)) {
            return $this->knownTablePresence[$table];
        }

        try {
            $this->knownTablePresence[$table] = Schema::hasTable($table);
        } catch (Throwable) {
            $this->knownTablePresence[$table] = false;
        }

        return $this->knownTablePresence[$table];
    }

    protected function canQueryColumn(string $table, string $column): bool
    {
        $cacheKey = "{$table}.{$column}";

        if (array_key_exists($cacheKey, $this->knownColumnPresence)) {
            return $this->knownColumnPresence[$cacheKey];
        }

        if (! $this->canQueryTable($table)) {
            $this->knownColumnPresence[$cacheKey] = false;

            return false;
        }

        try {
            $this->knownColumnPresence[$cacheKey] = Schema::hasColumn($table, $column);
        } catch (Throwable) {
            $this->knownColumnPresence[$cacheKey] = false;
        }

        return $this->knownColumnPresence[$cacheKey];
    }

    /**
     * @param  array<int, string>  $slugs
     * @return Collection<string, Page>
     */
    protected function publishedPagesBySlug(array $slugs): Collection
    {
        if (! $this->canQueryTable('pages')) {
            return collect();
        }

        $uniqueSlugs = array_values(array_unique($slugs));

        if ($uniqueSlugs === []) {
            return collect();
        }

        return Page::query()
            ->where('status', 'published')
            ->whereIn('slug', $uniqueSlugs)
            ->get()
            ->keyBy('slug');
    }

    protected function publishedPageBySlug(string $slug): ?Page
    {
        if (! $this->canQueryTable('pages')) {
            return null;
        }

        return Page::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array<int, string>  $menuBindings
     * @return Collection<string, Page>
     */
    protected function publishedPagesByMenuBinding(array $menuBindings): Collection
    {
        if (! $this->canQueryColumn('pages', 'menu_binding')) {
            return collect();
        }

        $uniqueBindings = array_values(array_unique(array_filter($menuBindings)));

        if ($uniqueBindings === []) {
            return collect();
        }

        return Page::query()
            ->where('status', 'published')
            ->whereIn('menu_binding', $uniqueBindings)
            ->get()
            ->keyBy('menu_binding');
    }

    protected function publishedPageByMenuBinding(string $menuBinding): ?Page
    {
        if (! $this->canQueryColumn('pages', 'menu_binding')) {
            return null;
        }

        return Page::query()
            ->where('status', 'published')
            ->where('menu_binding', $menuBinding)
            ->first();
    }

    protected function publishedPageByBindingOrSlug(?string $menuBinding, string $fallbackSlug): ?Page
    {
        if (filled($menuBinding)) {
            $boundPage = $this->publishedPageByMenuBinding($menuBinding);

            if ($boundPage) {
                return $boundPage;
            }
        }

        return $this->publishedPageBySlug($fallbackSlug);
    }

    protected function sanitizeGoogleDriveUrl(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $normalized = trim($url);

        if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($normalized, PHP_URL_SCHEME));

        if ($scheme !== 'https') {
            return null;
        }

        $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if (! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
            return null;
        }

        return $normalized;
    }

    protected function applyGoogleDriveDocumentUrlFilter(Builder $query, string $column = 'external_url'): Builder
    {
        return $query->where(function (Builder $urlQuery) use ($column): void {
            $urlQuery
                ->where($column, 'like', 'https://drive.google.com/%')
                ->orWhere($column, 'like', 'https://docs.google.com/%')
                ->orWhere($column, 'like', 'https://www.drive.google.com/%')
                ->orWhere($column, 'like', 'https://www.docs.google.com/%');
        });
    }

    protected function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        $publicCandidates = array_values(array_unique(array_filter([
            Str::startsWith($normalizedPath, 'public/')
                ? Str::after($normalizedPath, 'public/')
                : $normalizedPath,
        ])));

        if (Str::startsWith($normalizedPath, 'storage/')) {
            return '/'.$normalizedPath;
        }

        try {
            foreach ($publicCandidates as $candidate) {
                if (Storage::disk('public')->exists($candidate)) {
                    return Storage::disk('public')->url($candidate);
                }
            }
        } catch (Throwable) {
            // Ignore disk errors and continue with fallback strategy.
        }

        return '/storage/'.$normalizedPath;
    }

    protected function formatEventTimeRange(
        ?CarbonInterface $startsAt,
        ?CarbonInterface $endsAt,
        bool $isAllDay = false,
    ): ?string {
        if ($isAllDay) {
            return 'Todo el dia';
        }

        if (! $startsAt && ! $endsAt) {
            return null;
        }

        if ($startsAt && $endsAt) {
            return $startsAt->format('h:i A').' - '.$endsAt->format('h:i A');
        }

        return ($startsAt ?? $endsAt)?->format('h:i A');
    }

    protected function normalizeEventLocation(?string $location): ?string
    {
        $value = trim((string) $location);

        return $value !== '' ? $value : null;
    }
}
