<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\SubRegion;
use Illuminate\Database\Seeder;

class SubRegionCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $countries = Country::all();

        foreach ($countries as $c) {

            $sub = SubRegion::where('name', $c->sub_region_name)->first();

            // Zona
            $zone = [
                'Africa' => 1,
                'Europe' => 2,
                'Asia' => 3,
                'Americas' => 4,
                'Oceania' => 5,
            ];

            if (empty($sub)) {
                $sub = new SubRegion;
                $sub->name = $c->sub_region_name;
                $sub->geo_zone_id = $zone[$c->region_name];
                $sub->save();
            }

            $c->geo_zone_id = $zone[$c->region_name];
            $c->sub_region_id = $sub->id;
            $c->save();

        }

    }
}
