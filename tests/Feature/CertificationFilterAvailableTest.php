<?php

use Domain\Certifications\Models\Certification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('filterAvailable scope returns available certifications', function () {
    $available = Certification::factory()->create(['is_available' => true]);
    $unavailable = Certification::factory()->create(['is_available' => false]);

    $results = Certification::filterAvailable('1')->pluck('id');

    expect($results)->toContain($available->id)
        ->and($results)->not->toContain($unavailable->id);
});

test('filterAvailable scope returns unavailable certifications', function () {
    $available = Certification::factory()->create(['is_available' => true]);
    $unavailable = Certification::factory()->create(['is_available' => false]);

    $results = Certification::filterAvailable('0')->pluck('id');

    expect($results)->toContain($unavailable->id)
        ->and($results)->not->toContain($available->id);
});
