<?php

namespace Database\Seeders;

use App\Models\GeoZone;
use Illuminate\Database\Seeder;

class GeoZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $geo_zones = [
            [
                'name' => 'Africa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Europe',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Americas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Asia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Oceania',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        GeoZone::insert($geo_zones);
    }
}
