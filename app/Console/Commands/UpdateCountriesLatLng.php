<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCountriesLatLng extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:countries-lat-lng';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update countries latitude and longitude using Nominatim OpenStreetMap API';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $countries = Country::all(); // Country::where('id', '>=',  114)->get();

        foreach ($countries as $country) {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'country' => $country->name,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (count($data) > 0) {
                    $lat = $data[0]['lat'];
                    $lng = $data[0]['lon'];

                    $country->update([
                        'lat' => $lat,
                        'lng' => $lng,
                    ]);

                    $this->info("Updated latitude and longitude for {$country->name}");
                } else {
                    $this->warn("No data found for {$country->name}");
                }
            } else {
                $this->error("Failed to fetch data for {$country->name}");
            }
        }

        $this->info('Completed updating countries latitude and longitude.');
    }
}
