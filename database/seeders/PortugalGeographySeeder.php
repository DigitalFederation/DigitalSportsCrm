<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PortugalGeographySeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DistrictSeeder::class,
            ZoneSeeder::class,
        ]);
    }
}
