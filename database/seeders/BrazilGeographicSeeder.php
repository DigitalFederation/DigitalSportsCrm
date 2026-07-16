<?php

namespace Database\Seeders;

use App\Models\Country;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Seeds Brazil's geographic hierarchy from the official IBGE dataset:
 *   - Zones     = the 27 federative units (states + Federal District)
 *   - Districts = the 5571 municipalities, each attached to its state zone
 *
 * Source: https://servicodados.ibge.gov.br/api/v1/localidades/municipios
 * (the dataset behind https://www.ibge.gov.br/explica/codigos-dos-municipios.php)
 */
class BrazilGeographicSeeder extends Seeder
{
    private const CSV_PATH = 'database/data/brazil_municipalities.csv';

    public function run(): void
    {
        $rows = $this->readCsv();

        $brazilId = Country::query()->where('name', 'Brazil')->value('id');
        if (! $brazilId) {
            throw new RuntimeException('Country "Brazil" not found. Run the CountrySeeder first.');
        }

        $zoneIdByUf = $this->seedZones($rows);
        $this->seedDistricts($rows, $brazilId, $zoneIdByUf);
    }

    /**
     * @return list<array{ibge_code: string, name: string, uf: string, state_name: string}>
     */
    private function readCsv(): array
    {
        $path = base_path(self::CSV_PATH);
        if (! is_readable($path)) {
            throw new RuntimeException("Municipality dataset not found at {$path}.");
        }

        $handle = fopen($path, 'r');
        $rows = [];
        $isHeader = true;

        while (($data = fgetcsv($handle, 2000, ';', '"', '\\')) !== false) {
            if ($isHeader) {
                $isHeader = false;

                continue;
            }

            $rows[] = [
                'ibge_code' => $data[0],
                'name' => $data[1],
                'uf' => $data[2],
                'state_name' => $data[3],
            ];
        }

        fclose($handle);

        return $rows;
    }

    /**
     * One zone per federative unit, keyed by UF.
     *
     * @param  list<array{uf: string, state_name: string}>  $rows
     * @return array<string, int> UF => zone id
     */
    private function seedZones(array $rows): array
    {
        $states = [];
        foreach ($rows as $row) {
            $states[$row['uf']] = $row['state_name'];
        }
        ksort($states);

        $zoneIdByUf = [];
        foreach ($states as $uf => $stateName) {
            $zone = Zone::query()->updateOrCreate(
                ['code' => $uf],
                ['name' => $stateName, 'is_active' => true]
            );
            $zoneIdByUf[$uf] = $zone->id;
        }

        $this->command?->info('Zones (federative units): ' . count($zoneIdByUf));

        return $zoneIdByUf;
    }

    /**
     * One district per municipality, attached to its state zone.
     *
     * @param  list<array{ibge_code: string, name: string, uf: string}>  $rows
     * @param  array<string, int>  $zoneIdByUf
     */
    private function seedDistricts(array $rows, int $brazilId, array $zoneIdByUf): void
    {
        $now = now();

        $districts = array_map(fn (array $row): array => [
            'code' => $row['ibge_code'],
            'name' => $row['name'],
            'country_id' => $brazilId,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows);

        foreach (array_chunk($districts, 500) as $chunk) {
            District::query()->upsert($chunk, ['code'], ['name', 'country_id', 'is_active', 'updated_at']);
        }

        $districtIdByCode = District::query()
            ->whereIn('code', array_column($rows, 'ibge_code'))
            ->pluck('id', 'code');

        $pivot = [];
        foreach ($rows as $row) {
            $pivot[] = [
                'district_id' => $districtIdByCode[$row['ibge_code']],
                'zone_id' => $zoneIdByUf[$row['uf']],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($pivot, 500) as $chunk) {
            DB::table('district_zone')->upsert($chunk, ['district_id', 'zone_id'], ['updated_at']);
        }

        $this->command?->info('Districts (municipalities): ' . count($districts));
    }
}
