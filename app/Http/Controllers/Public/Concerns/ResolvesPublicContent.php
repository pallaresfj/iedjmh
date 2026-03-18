<?php

namespace App\Http\Controllers\Public\Concerns;

use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
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

        return '/storage/'.ltrim($path, '/');
    }
}
