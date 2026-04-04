<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::table('documents')
            ->select('id', 'external_url', 'file_path')
            ->orderBy('id')
            ->chunkById(500, function ($documents): void {
                $deleteIds = collect($documents)
                    ->filter(function (object $document): bool {
                        if (filled($document->file_path)) {
                            return true;
                        }

                        return ! $this->isValidGoogleDriveUrl($document->external_url);
                    })
                    ->pluck('id')
                    ->all();

                if ($deleteIds === []) {
                    return;
                }

                DB::table('documents')->whereIn('id', $deleteIds)->delete();
            });

        $hasFilePathColumn = Schema::hasColumn('documents', 'file_path');
        $hasFileDiskColumn = Schema::hasColumn('documents', 'file_disk');
        $hasExternalUrlColumn = Schema::hasColumn('documents', 'external_url');

        Schema::table('documents', function (Blueprint $table) use ($hasExternalUrlColumn, $hasFileDiskColumn, $hasFilePathColumn): void {
            if ($hasExternalUrlColumn) {
                $table->string('external_url', 2048)->nullable(false)->change();
            }

            if ($hasFilePathColumn) {
                $table->dropColumn('file_path');
            }

            if ($hasFileDiskColumn) {
                $table->dropColumn('file_disk');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        $hasFilePathColumn = Schema::hasColumn('documents', 'file_path');
        $hasFileDiskColumn = Schema::hasColumn('documents', 'file_disk');
        $hasExternalUrlColumn = Schema::hasColumn('documents', 'external_url');

        Schema::table('documents', function (Blueprint $table) use ($hasExternalUrlColumn, $hasFileDiskColumn, $hasFilePathColumn): void {
            if ($hasExternalUrlColumn) {
                $table->string('external_url', 2048)->nullable()->change();
            }

            if (! $hasFilePathColumn) {
                $table->string('file_path')->nullable();
            }

            if (! $hasFileDiskColumn) {
                $table->string('file_disk')->default('public');
            }
        });
    }

    private function isValidGoogleDriveUrl(mixed $url): bool
    {
        if (! is_string($url)) {
            return false;
        }

        $normalized = trim($url);

        if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($normalized, PHP_URL_SCHEME));

        if ($scheme !== 'https') {
            return false;
        }

        $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        return in_array($host, ['drive.google.com', 'docs.google.com'], true);
    }
};
