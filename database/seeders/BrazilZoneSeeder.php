<?php

namespace Database\Seeders;

use Domain\Geographic\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the 26 Brazilian states plus Distrito Federal as geographic zones.
 *
 * Idempotent/non-destructive policy:
 * - code is treated as the stable natural key;
 * - existing rows are never updated or deleted;
 * - only missing rows are inserted.
 */
class BrazilZoneSeeder extends Seeder
{
    /**
     * @return array<int, array{code: string, name: string}>
     */
    public static function data(): array
    {
        return [
            ['code' => 'AC', 'name' => 'Acre'],
            ['code' => 'AL', 'name' => 'Alagoas'],
            ['code' => 'AM', 'name' => 'Amazonas'],
            ['code' => 'AP', 'name' => 'Amapá'],
            ['code' => 'BA', 'name' => 'Bahia'],
            ['code' => 'CE', 'name' => 'Ceará'],
            ['code' => 'DF', 'name' => 'Distrito Federal'],
            ['code' => 'ES', 'name' => 'Espírito Santo'],
            ['code' => 'GO', 'name' => 'Goiás'],
            ['code' => 'MA', 'name' => 'Maranhão'],
            ['code' => 'MG', 'name' => 'Minas Gerais'],
            ['code' => 'MS', 'name' => 'Mato Grosso do Sul'],
            ['code' => 'MT', 'name' => 'Mato Grosso'],
            ['code' => 'PA', 'name' => 'Pará'],
            ['code' => 'PB', 'name' => 'Paraíba'],
            ['code' => 'PE', 'name' => 'Pernambuco'],
            ['code' => 'PI', 'name' => 'Piauí'],
            ['code' => 'PR', 'name' => 'Paraná'],
            ['code' => 'RJ', 'name' => 'Rio de Janeiro'],
            ['code' => 'RN', 'name' => 'Rio Grande do Norte'],
            ['code' => 'RO', 'name' => 'Rondônia'],
            ['code' => 'RR', 'name' => 'Roraima'],
            ['code' => 'RS', 'name' => 'Rio Grande do Sul'],
            ['code' => 'SC', 'name' => 'Santa Catarina'],
            ['code' => 'SE', 'name' => 'Sergipe'],
            ['code' => 'SP', 'name' => 'São Paulo'],
            ['code' => 'TO', 'name' => 'Tocantins'],
        ];
    }

    public function run(): void
    {
        $zones = static::data();
        $codes = array_column($zones, 'code');
        $now = now();
        $inserted = 0;
        $preserved = 0;
        $nameConflicts = 0;

        DB::transaction(function () use ($zones, $codes, $now, &$inserted, &$preserved, &$nameConflicts): void {
            $existing = Zone::query()
                ->whereIn('code', $codes)
                ->get(['code', 'name'])
                ->keyBy('code');

            $rows = [];

            foreach ($zones as $zone) {
                $current = $existing->get($zone['code']);

                if ($current) {
                    $preserved++;

                    if ($current->name !== $zone['name']) {
                        $nameConflicts++;
                    }

                    continue;
                }

                $rows[] = [
                    'name' => $zone['name'],
                    'code' => $zone['code'],
                    'description' => null,
                    'is_active' => true,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('zones')->insert($chunk);
                $inserted += count($chunk);
            }
        });

        $this->command?->info("BrazilZoneSeeder: {$inserted} inserted; {$preserved} existing rows preserved.");

        if ($nameConflicts > 0) {
            $this->command?->warn("BrazilZoneSeeder: {$nameConflicts} existing zone(s) have a different name and were intentionally left unchanged.");
        }
    }
}
