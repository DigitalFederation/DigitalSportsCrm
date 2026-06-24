<?php

namespace Database\Seeders;

use Domain\Licenses\Models\LicenseType;
use Illuminate\Database\Seeder;

class LicenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LicenseType::firstOrCreate(
            ['name' => 'entity'],
            ['is_individual' => false]
        );

        LicenseType::firstOrCreate(
            ['name' => 'individual'],
            ['is_individual' => true]
        );
    }
}
