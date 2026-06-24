<?php

namespace App\Console\Commands;

use Domain\Federations\Models\Federation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PopulateFederationCoordinates extends Command
{
    protected $signature = 'federations:geocode {--retry-failed : Retry only failed geocoding attempts}';
    protected $description = 'Populate federation coordinates using OpenStreetMap Nominatim';

    private function buildSearchQuery(Federation $federation): string
    {
        $components = array_filter([
            $federation->address,
            $federation->location,
            $federation->country->name ?? null,
        ]);

        return implode(', ', $components);
    }

    private function geocodeAddress(string $address): ?array
    {

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Federation Geocoding Script',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'q' => $address,
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && ! empty($response->json())) {
                $data = $response->json()[0];

                return [
                    'lat' => $data['lat'],
                    'lng' => $data['lon'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
        }

        return null;
    }

    public function handle()
    {
        $query = Federation::query()
            ->when(! $this->option('retry-failed'), function ($query) {
                return $query->whereNull('lat')->whereNull('lng');
            });

        $total = $query->count();
        $this->info("Processing {$total} federations...");

        $bar = $this->output->createProgressBar($total);

        foreach ($query->cursor() as $federation) {
            $searchQuery = $this->buildSearchQuery($federation);
            $this->info("\nProcessing: {$searchQuery}");

            $coordinates = $this->geocodeAddress($searchQuery);

            if ($coordinates) {
                $federation->update([
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng'],
                ]);
                $this->info(" - Updated with coordinates: {$coordinates['lat']}, {$coordinates['lng']}");
            } else {
                Log::warning("Failed to geocode federation ID {$federation->id}: {$searchQuery}");
                $this->error(' - Failed to geocode');
            }

            // Respect Nominatim usage policy
            sleep(1);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Geocoding completed!');
    }
}
