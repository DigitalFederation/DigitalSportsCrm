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
        $brasil = Country::where('ioc', 'BRA')->first();

        if (! $brasil) {
            $this->command->warn('Brazil country not found. Skipping state seeding.');

            return;
        }

        $districts = [
            ['name' => 'Acre', 'code' => 'AC'],
            ['name' => 'Alagoas', 'code' => 'AL'],
            ['name' => 'Amapá', 'code' => 'AP'],
            ['name' => 'Amazonas', 'code' => 'AM'],
            ['name' => 'Bahia', 'code' => 'BA'],
            ['name' => 'Ceará', 'code' => 'CE'],
            ['name' => 'Distrito Federal', 'code' => 'DF'],
            ['name' => 'Espírito Santo', 'code' => 'ES'],
            ['name' => 'Goiás', 'code' => 'GO'],
            ['name' => 'Maranhão', 'code' => 'MA'],
            ['name' => 'Mato Grosso', 'code' => 'MT'],
            ['name' => 'Mato Grosso do Sul', 'code' => 'MS'],
            ['name' => 'Minas Gerais', 'code' => 'MG'],
            ['name' => 'Pará', 'code' => 'PA'],
            ['name' => 'Paraíba', 'code' => 'PB'],
            ['name' => 'Paraná', 'code' => 'PR'],
            ['name' => 'Pernambuco', 'code' => 'PE'],
            ['name' => 'Piauí', 'code' => 'PI'],
            ['name' => 'Rio de Janeiro', 'code' => 'RJ'],
            ['name' => 'Rio Grande do Norte', 'code' => 'RN'],
            ['name' => 'Rio Grande do Sul', 'code' => 'RS'],
            ['name' => 'Rondônia', 'code' => 'RO'],
            ['name' => 'Roraima', 'code' => 'RR'],
            ['name' => 'Santa Catarina', 'code' => 'SC'],
            ['name' => 'São Paulo', 'code' => 'SP'],
            ['name' => 'Sergipe', 'code' => 'SE'],
            ['name' => 'Tocantins', 'code' => 'TO'],
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(
                ['code' => $district['code']],
                [
                    'name' => $district['name'],
                    'country_id' => $brasil->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
