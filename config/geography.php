<?php

use Database\Seeders\BrazilGeographySeeder;
use Database\Seeders\PortugalGeographySeeder;

return [
    'dataset' => env('GEOGRAPHY_DATASET', 'portugal'),

    'datasets' => [
        'portugal' => PortugalGeographySeeder::class,
        'brazil' => BrazilGeographySeeder::class,
    ],
];
