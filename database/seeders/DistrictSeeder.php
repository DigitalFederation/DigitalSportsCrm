<?php

namespace Database\Seeders;

use App\Models\Country;
use Domain\Geographic\Models\District;
use Illuminate\Database\Seeder;

/**
 * Seeds Portuguese districts.
 */
class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $portugal = Country::where('ioc', 'POR')->first();

        if (! $portugal) {
            $this->command->warn('Portugal country not found. Skipping district seeding.');

            return;
        }

        $districts = [
            ['name' => 'Açores', 'code' => 'A'],
            ['name' => 'Aveiro', 'code' => 'AV'],
            ['name' => 'Beja', 'code' => 'BE'],
            ['name' => 'Braga', 'code' => 'BR'],
            ['name' => 'Bragança', 'code' => 'BG'],
            ['name' => 'Castelo Branco', 'code' => 'CB'],
            ['name' => 'Coimbra', 'code' => 'C'],
            ['name' => 'Évora', 'code' => 'E'],
            ['name' => 'Faro', 'code' => 'FA'],
            ['name' => 'Guarda', 'code' => 'GD'],
            ['name' => 'Leiria', 'code' => 'LE'],
            ['name' => 'Lisboa', 'code' => 'L'],
            ['name' => 'Madeira', 'code' => 'M'],
            ['name' => 'Portalegre', 'code' => 'PT'],
            ['name' => 'Porto', 'code' => 'P'],
            ['name' => 'Santarém', 'code' => 'SA'],
            ['name' => 'Setúbal', 'code' => 'SE'],
            ['name' => 'Viana do Castelo', 'code' => 'VC'],
            ['name' => 'Vila Real', 'code' => 'VR'],
            ['name' => 'Viseu', 'code' => 'VI'],
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(
                ['code' => $district['code']],
                [
                    'name' => $district['name'],
                    'country_id' => $portugal->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
