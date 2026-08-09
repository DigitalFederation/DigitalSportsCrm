<?php

namespace Database\Seeders;

use App\Models\Country;
use Domain\Geographic\Enums\ZoneKind;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BrazilGeographySeeder extends Seeder
{
    private const EXPECTED_STATE_COUNT = 27;

    private const EXPECTED_LOCALITY_COUNT = 5571;

    public function run(): void
    {
        $country = Country::query()->where('iso', 'BR')->sole();
        $states = $this->readCsv('brazil/states.csv');
        $localities = $this->readCsv('brazil/municipalities.csv');

        if (count($states) !== self::EXPECTED_STATE_COUNT || count($localities) !== self::EXPECTED_LOCALITY_COUNT) {
            throw new RuntimeException('The bundled Brazil geography dataset has an unexpected record count.');
        }

        DB::transaction(function () use ($country, $states, $localities): void {
            $now = now();

            Zone::query()->upsert(
                array_map(fn (array $state): array => [
                    'country_id' => $country->id,
                    'name' => $state['name'],
                    'code' => 'BR-'.$state['abbreviation'],
                    'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1->value,
                    'external_code' => $state['ibge_code'],
                    'description' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $states),
                ['code'],
                ['country_id', 'name', 'kind', 'external_code', 'is_active', 'updated_at']
            );

            $stateIds = Zone::query()
                ->where('country_id', $country->id)
                ->administrativeLevelOne()
                ->pluck('id', 'external_code');

            foreach (array_chunk($localities, 500) as $chunk) {
                District::query()->upsert(
                    array_map(function (array $locality) use ($country, $stateIds, $now): array {
                        $stateId = $stateIds->get($locality['state_ibge_code']);

                        if (! is_int($stateId)) {
                            throw new RuntimeException(
                                "No state found for IBGE locality [{$locality['ibge_code']}]."
                            );
                        }

                        return [
                            'country_id' => $country->id,
                            'administrative_zone_id' => $stateId,
                            'name' => $locality['name'],
                            'code' => $locality['ibge_code'],
                            'description' => null,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $chunk),
                    ['code'],
                    ['country_id', 'administrative_zone_id', 'name', 'is_active', 'updated_at']
                );
            }
        });
    }

    /**
     * @return list<array<string, string>>
     */
    private function readCsv(string $relativePath): array
    {
        $path = database_path('data/'.$relativePath);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to read geography dataset [{$path}].");
        }

        $headers = fgetcsv($handle, 0, ';');

        if (! is_array($headers)) {
            fclose($handle);

            throw new RuntimeException("Geography dataset [{$path}] has no header row.");
        }

        $rows = [];

        while (($values = fgetcsv($handle, 0, ';')) !== false) {
            if (count($headers) !== count($values)) {
                fclose($handle);

                throw new RuntimeException("Geography dataset [{$path}] contains a malformed row.");
            }

            $rows[] = array_combine($headers, $values);
        }

        fclose($handle);

        return $rows;
    }
}
