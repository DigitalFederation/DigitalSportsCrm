<?php

namespace App\Console\Commands;

use Domain\Entities\Models\Entity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PopulateEntityCoordinates extends Command
{
    protected $signature = 'entities:geocode {--retry-failed : Retry only failed geocoding attempts}';
    protected $description = 'Populate entities coordinates using OpenStreetMap Nominatim';

    private function buildSearchQuery(Entity $entity): string
    {
        $components = array_filter([
            $entity->address,
            $entity->location,
            $entity->country->name ?? null,
        ]);

        return implode(', ', $components);
    }

    private function geocodeAddress(string $address): ?array
    {

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Entity Geocoding Script',
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
        $query = Entity::query()
            ->when(! $this->option('retry-failed'), function ($query) {
                return $query->whereNull('lat')->whereNull('lng');
            });

        $total = $query->count();
        $this->info("Processing {$total} entities...");

        $bar = $this->output->createProgressBar($total);

        foreach ($query->cursor() as $entity) {
            $searchQuery = $this->buildSearchQuery($entity);
            $this->info("\nProcessing: {$searchQuery}");

            $coordinates = $this->geocodeAddress($searchQuery);

            if ($coordinates) {
                $entity->update([
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng'],
                ]);
                $this->info(" - Updated with coordinates: {$coordinates['lat']}, {$coordinates['lng']}");
            } else {
                Log::warning("Failed to geocode entity ID {$entity->id}: {$searchQuery}");
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
