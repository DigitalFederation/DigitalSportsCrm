<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StartupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CommitteeSeeder::class,
            RoleAndPermissionSeeder::class,
            CountrySeeder::class,
            LanguagesTableSeeder::class,
            LicenseSeeder::class,
            MembershipPlanSeeder::class,
        ]);
    }
}
