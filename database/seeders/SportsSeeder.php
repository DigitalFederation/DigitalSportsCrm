<?php

namespace Database\Seeders;

use App\Models\Sport;
use Domain\EvtEvents\Models\Sport as EvtSport;
use Illuminate\Database\Seeder;

class SportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sports = [
            ['name' => 'Finswimming'],
            ['name' => 'Freediving'],
            ['name' => 'Aquathlon'],
            ['name' => 'Underwater Hockey'],
            ['name' => 'Underwater Rugby'],
            ['name' => 'Target Shooting'],
            ['name' => 'Sport Diving'],
            ['name' => 'Spearfishing'],
            ['name' => 'Orienteering'],
            ['name' => 'Visual'],
        ];

        // Seed the 'sports' table (App\Models\Sport)
        foreach ($sports as $sport) {
            Sport::firstOrCreate($sport);
        }

        // Seed the 'evt_sports' table (Domain\EvtEvents\Models\Sport)
        foreach ($sports as $sport) {
            EvtSport::firstOrCreate($sport);
        }
    }
}
