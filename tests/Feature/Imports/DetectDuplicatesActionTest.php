<?php

use App\Models\Country;
use Domain\Imports\Actions\DetectDuplicatesAction;
use Domain\Individuals\Models\Individual;

beforeEach(function () {
    $this->action = new DetectDuplicatesAction;
    $this->country = Country::factory()->create();

    // Create test individuals
    $this->existingIndividual = Individual::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'John',
        'surname' => 'Doe',
        'birthdate' => '1990-01-01',
        'country_id' => $this->country->id,
    ]);
});

test('detects duplicates by email', function () {
    $individuals = [
        [
            'email' => 'existing@example.com',
            'name' => 'Different',
            'surname' => 'Name',
            'birthdate' => '2000-01-01',
            'country_id' => $this->country->id,
        ],
        [
            'email' => 'new@example.com',
            'name' => 'New',
            'surname' => 'User',
            'birthdate' => '1995-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $duplicates = $this->action->execute($individuals);

    expect($duplicates)->toHaveCount(1)
        ->and($duplicates[0]['individual']->id)->toBe($this->existingIndividual->id)
        ->and($duplicates[0]['match_type'])->toBe('email');
});

test('detects duplicates by composite key', function () {
    $individuals = [
        [
            'email' => 'different@example.com',
            'name' => 'John',
            'surname' => 'Doe',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $duplicates = $this->action->execute($individuals);

    expect($duplicates)->toHaveCount(1)
        ->and($duplicates[0]['individual']->id)->toBe($this->existingIndividual->id)
        ->and($duplicates[0]['match_type'])->toBe('composite');
});

test('email match takes priority over composite match', function () {
    // Create another individual with same composite but different email
    Individual::factory()->create([
        'email' => 'another@example.com',
        'name' => 'John',
        'surname' => 'Doe',
        'birthdate' => '1990-01-01',
        'country_id' => $this->country->id,
    ]);

    $individuals = [
        [
            'email' => 'existing@example.com', // Email match
            'name' => 'John',
            'surname' => 'Doe',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $duplicates = $this->action->execute($individuals);

    expect($duplicates[0]['match_type'])->toBe('email');
});

test('checks if individual exists by email', function () {
    expect($this->action->existsByEmail('existing@example.com'))->toBeTrue()
        ->and($this->action->existsByEmail('nonexistent@example.com'))->toBeFalse();
});

test('provides duplicate statistics', function () {
    Individual::factory()->create([
        'email' => 'another@example.com',
        'name' => 'Jane',
        'surname' => 'Smith',
        'birthdate' => '1992-01-01',
        'country_id' => $this->country->id,
    ]);

    $individuals = [
        [
            'email' => 'existing@example.com', // Email duplicate
            'name' => 'Test',
            'surname' => 'User',
            'birthdate' => '2000-01-01',
            'country_id' => $this->country->id,
        ],
        [
            'email' => 'new@example.com',
            'name' => 'Jane',
            'surname' => 'Smith',
            'birthdate' => '1992-01-01',
            'country_id' => $this->country->id, // Composite duplicate
        ],
        [
            'email' => 'unique@example.com',
            'name' => 'Unique',
            'surname' => 'Person',
            'birthdate' => '1995-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $stats = $this->action->getStatistics($individuals);

    expect($stats['total_duplicates'])->toBe(2)
        ->and($stats['email_duplicates'])->toBe(1)
        ->and($stats['composite_duplicates'])->toBe(1)
        ->and($stats['unique_records'])->toBe(1);
});

test('handles empty input gracefully', function () {
    $duplicates = $this->action->execute([]);

    expect($duplicates)->toBe([])
        ->and($this->action->getStatistics([]))->toBe([
            'total_duplicates' => 0,
            'email_duplicates' => 0,
            'composite_duplicates' => 0,
            'unique_records' => 0,
        ]);
});

test('loads user relationship with duplicates', function () {
    $individuals = [
        [
            'email' => 'existing@example.com',
            'name' => 'Test',
            'surname' => 'User',
            'birthdate' => '2000-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $duplicates = $this->action->execute($individuals);

    expect($duplicates[0]['individual']->relationLoaded('user'))->toBeTrue();
});
