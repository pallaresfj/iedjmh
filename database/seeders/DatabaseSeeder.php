<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ShieldRolesAndUsersSeeder::class,
        ]);

        if (! app()->isProduction() || (bool) env('SEED_DEMO_CONTENT', false)) {
            $this->call([
                DemoContentSeeder::class,
            ]);
        }
    }
}
