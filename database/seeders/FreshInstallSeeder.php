<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Minimal seeder for fresh installations.
 *
 * Seeds only essential base data required for the application to function.
 * Licenses, certifications, membership plans, and sports should be
 * configured by the PM through the admin interface.
 */
class FreshInstallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(ZoneSeeder::class);
        $this->call(UserGroupSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(ProfessionalRoleSeeder::class);
        $this->call(SportsSeeder::class);
        $this->call(LicenseTypeSeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(PaymentMethodSeeder::class);

        // Optional first admin account. No-op unless SEED_DEFAULT_ADMIN=true
        // and DEFAULT_ADMIN_PASSWORD are set. Must run after
        // RoleAndPermissionSeeder and UserGroupSeeder.
        $this->call(UserSeeder::class);
    }
}
