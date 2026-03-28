<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('area_plans') ||
            ! Schema::hasColumn('area_plans', 'responsible_teachers') ||
            ! Schema::hasTable('staff_members') ||
            ! Schema::hasTable('area_plan_staff_member')
        ) {
            return;
        }

        $teachersByNormalizedName = DB::table('staff_members')
            ->select(['id', 'full_name'])
            ->where('staff_group', 'teacher')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get()
            ->mapWithKeys(function (object $teacher): array {
                $normalizedName = self::normalizeName($teacher->full_name);

                if ($normalizedName === '') {
                    return [];
                }

                return [$normalizedName => (int) $teacher->id];
            })
            ->all();

        $plans = DB::table('area_plans')
            ->select(['id', 'responsible_teachers'])
            ->whereNull('deleted_at')
            ->get();

        $unmatchedNames = [];
        $timestamp = now();

        foreach ($plans as $plan) {
            $teacherNames = collect(explode(',', (string) $plan->responsible_teachers))
                ->map(fn (string $name): string => trim($name))
                ->filter()
                ->values();

            if ($teacherNames->isEmpty()) {
                continue;
            }

            $syncPayload = [];

            foreach ($teacherNames as $index => $teacherName) {
                $teacherId = $teachersByNormalizedName[self::normalizeName($teacherName)] ?? null;

                if (! $teacherId) {
                    $unmatchedNames[] = $teacherName;

                    continue;
                }

                $syncPayload[] = [
                    'area_plan_id' => $plan->id,
                    'staff_member_id' => $teacherId,
                    'sort_order' => $index,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            if ($syncPayload === []) {
                continue;
            }

            DB::table('area_plan_staff_member')->upsert(
                $syncPayload,
                ['area_plan_id', 'staff_member_id'],
                ['sort_order', 'updated_at'],
            );
        }

        if ($unmatchedNames === []) {
            return;
        }

        $message = 'Area plan teacher names not mapped to published staff members: '.implode(
            ', ',
            collect($unmatchedNames)->unique()->sort()->values()->all(),
        );

        logger()->warning($message);

        if (app()->runningInConsole()) {
            echo PHP_EOL.$message.PHP_EOL;
        }
    }

    public function down(): void
    {
        // No-op: this migration only backfills existing pivot rows.
    }

    private static function normalizeName(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();
    }
};
