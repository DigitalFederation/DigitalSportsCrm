<?php

namespace Database\Seeders;

use App\Models\Country;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Seeds the municipality/state relationships in district_zone.
 *
 * The original dump IDs are not reused. A district is resolved by its IBGE
 * code and a zone by its UF code, making this seeder safe on databases whose
 * auto-increment IDs differ from the source dump.
 *
 * Existing pivot rows are preserved; only missing pairs are inserted.
 */
class BrazilDistrictZoneSeeder extends Seeder
{
    private const CHUNK_SIZE = 500;

    /**
     * First two digits of the 7-digit IBGE municipality code => UF code.
     * This mapping was validated against every relationship in district_zone.sql.
     *
     * @var array<string, string>
     */
    private const IBGE_PREFIX_TO_UF = [
        '11' => 'RO',
        '12' => 'AC',
        '13' => 'AM',
        '14' => 'RR',
        '15' => 'PA',
        '16' => 'AP',
        '17' => 'TO',
        '21' => 'MA',
        '22' => 'PI',
        '23' => 'CE',
        '24' => 'RN',
        '25' => 'PB',
        '26' => 'PE',
        '27' => 'AL',
        '28' => 'SE',
        '29' => 'BA',
        '31' => 'MG',
        '32' => 'ES',
        '33' => 'RJ',
        '35' => 'SP',
        '41' => 'PR',
        '42' => 'SC',
        '43' => 'RS',
        '50' => 'MS',
        '51' => 'MT',
        '52' => 'GO',
        '53' => 'DF',
    ];

    public function run(): void
    {
        // Makes this seeder independently runnable while retaining idempotency.
        $this->call([
            BrazilZoneSeeder::class,
            BrazilDistrictSeeder::class,
        ]);

        $brasil = Country::query()->where('ioc', 'BRA')->first();

        if (! $brasil) {
            $this->command?->warn('BrazilDistrictZoneSeeder: country BRA was not found. Nothing was inserted.');

            return;
        }

        $zoneCodes = array_values(self::IBGE_PREFIX_TO_UF);
        $zoneIds = Zone::query()
            ->whereIn('code', $zoneCodes)
            ->pluck('id', 'code');

        $missingZoneCodes = array_values(array_diff($zoneCodes, $zoneIds->keys()->all()));

        if ($missingZoneCodes !== []) {
            throw new RuntimeException(
                'BrazilDistrictZoneSeeder: missing Brazilian zones: '.implode(', ', $missingZoneCodes)
            );
        }

        $inserted = 0;
        $preserved = 0;
        $missingOrConflictingDistricts = 0;
        $now = now();

        DB::transaction(function () use ($brasil, $zoneIds, $now, &$inserted, &$preserved, &$missingOrConflictingDistricts): void {
            foreach (array_chunk(BrazilDistrictSeeder::data(), self::CHUNK_SIZE) as $districtData) {
                $codes = array_column($districtData, 'code');

                // country_id is checked deliberately: if a pre-existing row with the
                // same code belongs to another country, this seeder will not touch it.
                $districts = District::query()
                    ->whereIn('code', $codes)
                    ->where('country_id', $brasil->id)
                    ->get(['id', 'code']);

                $districtIdsByCode = $districts->pluck('id', 'code');
                $districtIds = $districts->pluck('id')->all();

                $existingPairs = [];

                if ($districtIds !== []) {
                    DB::table('district_zone')
                        ->whereIn('district_id', $districtIds)
                        ->get(['district_id', 'zone_id'])
                        ->each(function ($row) use (&$existingPairs): void {
                            $existingPairs[$row->district_id.':'.$row->zone_id] = true;
                        });
                }

                $rows = [];

                foreach ($districtData as $district) {
                    $districtId = $districtIdsByCode->get($district['code']);

                    if (! $districtId) {
                        $missingOrConflictingDistricts++;
                        continue;
                    }

                    $prefix = substr($district['code'], 0, 2);
                    $zoneCode = self::IBGE_PREFIX_TO_UF[$prefix] ?? null;

                    if (! $zoneCode) {
                        throw new RuntimeException("BrazilDistrictZoneSeeder: unknown IBGE UF prefix {$prefix} for district {$district['code']}.");
                    }

                    $zoneId = $zoneIds->get($zoneCode);
                    $key = $districtId.':'.$zoneId;

                    if (isset($existingPairs[$key])) {
                        $preserved++;
                        continue;
                    }

                    $rows[] = [
                        'district_id' => $districtId,
                        'zone_id' => $zoneId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('district_zone')->insert($rows);
                    $inserted += count($rows);
                }
            }
        });

        $this->command?->info("BrazilDistrictZoneSeeder: {$inserted} relationship(s) inserted; {$preserved} existing relationship(s) preserved.");

        if ($missingOrConflictingDistricts > 0) {
            $this->command?->warn("BrazilDistrictZoneSeeder: {$missingOrConflictingDistricts} district(s) could not be linked because the expected Brazilian district row was not available. Existing conflicting rows were not modified.");
        }
    }
}
