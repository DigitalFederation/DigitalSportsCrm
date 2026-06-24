<?php

namespace Database\Seeders;

use Domain\Geographic\Models\Zone;
use Illuminate\Database\Seeder;

/**
 * Seeds Portuguese geographic zones.
 */
class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            ['name' => 'Zona Norte', 'code' => 'ZN'],
            ['name' => 'Zona Centro', 'code' => 'ZC'],
            ['name' => 'Zona Sul', 'code' => 'ZS'],
            ['name' => 'Açores', 'code' => 'A'],
            ['name' => 'Madeira', 'code' => 'M'],
        ];

        foreach ($zones as $zone) {
            Zone::updateOrCreate(
                ['code' => $zone['code']],
                [
                    'name' => $zone['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
