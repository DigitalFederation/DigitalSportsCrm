<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(UserGroupSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(AttachmentCategoriesSeeder::class);
        $this->call(MembershipPlanSeeder::class);
        $this->call(ProfessionalRoleSeeder::class);
        $this->call(SportsSeeder::class);
        $this->call(LicenseSeeder::class);
        $this->call(InternationalLicenseSeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(GeoZoneSeeder::class);
        $this->call(SubRegionCountrySeeder::class);
        $this->call(RoleMappingSeeder::class);

        // Navigation menus (DB-driven sidebar). Without this a fresh install
        // renders no sidebar at all.
        $this->call(MenuSeeder::class);

        // Optional first admin account. No-op unless SEED_DEFAULT_ADMIN=true
        // and DEFAULT_ADMIN_PASSWORD are set. Must run after
        // RoleAndPermissionSeeder and UserGroupSeeder.
        $this->call(UserSeeder::class);
    }
}
