<?php

namespace App\Http\Controllers\Public\Concerns;

use App\Models\Page;
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
        $localCandidates = array_values(array_unique(array_filter([
            $normalizedPath,
            Str::startsWith($normalizedPath, 'public/')
                ? Str::after($normalizedPath, 'public/')
                : null,
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

        try {
            foreach ($localCandidates as $candidate) {
                if (Storage::disk('local')->exists($candidate)) {
                    return Storage::disk('local')->temporaryUrl($candidate, now()->addMinutes(30));
                }
            }
        } catch (Throwable) {
            // Ignore disk errors and continue with fallback strategy.
        }

        return '/storage/'.$normalizedPath;
    }
}
