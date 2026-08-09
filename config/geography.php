<?php

use Database\Seeders\PortugalGeographySeeder;

return [
    'dataset' => env('GEOGRAPHY_DATASET', 'portugal'),

    'datasets' => [
        'portugal' => PortugalGeographySeeder::class,
    ],
];
