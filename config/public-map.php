<?php

use App\Enums\CommitteeCodeEnum;

$countryId = env('PUBLIC_MAP_COUNTRY_ID');

return [
    'enabled' => env('PUBLIC_MAP_ENABLED', true),
    'country_id' => $countryId === null || $countryId === '' ? null : (int) $countryId,
    'center' => [
        'lat' => (float) env('PUBLIC_MAP_CENTER_LAT', 0),
        'lng' => (float) env('PUBLIC_MAP_CENTER_LNG', 0),
        'zoom' => (int) env('PUBLIC_MAP_CENTER_ZOOM', 2),
    ],
    'max_results' => (int) env('PUBLIC_MAP_MAX_RESULTS', 500),
    'include_federations' => env('PUBLIC_MAP_INCLUDE_FEDERATIONS', false),
    'show_contact_details' => env('PUBLIC_MAP_SHOW_CONTACT_DETAILS', false),
    'committee_codes' => [
        'sport' => env('PUBLIC_MAP_SPORT_COMMITTEE_CODE', CommitteeCodeEnum::Sport->value),
        'diving_services' => env('PUBLIC_MAP_DIVING_SERVICES_COMMITTEE_CODE', CommitteeCodeEnum::DivingServices->value),
    ],
];
