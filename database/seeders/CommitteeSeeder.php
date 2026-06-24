<?php

namespace Database\Seeders;

use App\Models\Committee;
use Illuminate\Database\Seeder;

class CommitteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Committees are deployment-defined in config/committees.php — each entry has a
     * `code`, `name`, and `is_international` flag. See
     * docs/architecture/02-committee-structure.md.
     */
    public function run(): void
    {
        foreach (config('committees.list', []) as $committee) {
            Committee::updateOrCreate(
                ['code' => $committee['code']],
                [
                    'name' => $committee['name'],
                    'is_international' => $committee['is_international'] ?? false,
                ]
            );
        }
    }
}
