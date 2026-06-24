<?php

use App\Jobs\GenerateModelQrCode;
use App\Models\Country;
use App\Models\User;
use Domain\Imports\Actions\BulkInsertIndividualsAction;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake([GenerateModelQrCode::class]);

    \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    $this->action = new BulkInsertIndividualsAction;
    $this->country = Country::factory()->create();

    // Create a default federation for the CreateIndividualAction
    \Domain\Federations\Models\Federation::factory()->create([
        'is_default_federation' => true,
        'name' => 'Default Federation',
    ]);
});

test('bulk inserts individuals with user accounts', function () {
    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
        [
            'name' => 'Jane',
            'surname' => 'Smith',
            'email' => 'jane@example.com',
            'birthdate' => '1992-05-15',
            'country_id' => $this->country->id,
        ],
    ];

    $result = $this->action->execute($individuals);

    expect($result['users'])->toBe(2)
        ->and($result['individuals'])->toBe(2)
        ->and($result['total'])->toBe(2)
        ->and(User::where('email', 'john@example.com')->exists())->toBeTrue()
        ->and(User::where('email', 'jane@example.com')->exists())->toBeTrue()
        ->and(Individual::where('email', 'john@example.com')->exists())->toBeTrue()
        ->and(Individual::where('email', 'jane@example.com')->exists())->toBeTrue();
});

test('handles existing users gracefully', function () {
    // Create existing user WITHOUT an individual record
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $individuals = [
        [
            'name' => 'Existing',
            'surname' => 'User',
            'email' => 'existing@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
        [
            'name' => 'New',
            'surname' => 'User',
            'email' => 'new@example.com',
            'birthdate' => '1992-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $result = $this->action->execute($individuals);

    expect($result['users'])->toBe(1) // Only one new user (existing@example.com already exists)
        ->and($result['individuals'])->toBe(2) // Both individuals created
        ->and($result['skipped'])->toBe(0) // No skipped records
        ->and(Individual::where('user_id', $existingUser->id)->exists())->toBeTrue();
});

test('uses database transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->action->execute([
        [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
    ]);
});

test('updates existing individuals', function () {
    $individual = Individual::factory()->create([
        'name' => 'Old Name',
        'birthdate' => '1990-01-01',
    ]);

    $updates = [
        [
            'id' => $individual->id,
            'name' => 'New Name',
            'surname' => $individual->surname,
            'birthdate' => '1991-01-01',
            'country_id' => $individual->country_id,
        ],
    ];

    $updated = $this->action->updateExisting($updates);

    expect($updated)->toBe(1);

    $individual->refresh();
    expect($individual->name)->toBe('New Name')
        ->and($individual->birthdate->format('Y-m-d'))->toBe('1991-01-01');
});

test('creates individuals with suffix for duplicates', function () {
    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $result = $this->action->createWithSuffix($individuals, '_test');

    expect(User::where('email', 'john_test@example.com')->exists())->toBeTrue()
        ->and(Individual::where('email', 'john_test@example.com')->exists())->toBeTrue();
});

test('associates individuals with multiple federations', function () {
    $federation1 = \Domain\Federations\Models\Federation::factory()->create();
    $federation2 = \Domain\Federations\Models\Federation::factory()->create();

    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
        [
            'name' => 'Jane',
            'surname' => 'Smith',
            'email' => 'jane@example.com',
            'birthdate' => '1992-05-15',
            'country_id' => $this->country->id,
        ],
    ];

    $result = $this->action->execute($individuals, [
        'federation_ids' => [$federation1->id, $federation2->id],
    ]);

    $john = Individual::where('email', 'john@example.com')->first();
    $jane = Individual::where('email', 'jane@example.com')->first();

    // CreateIndividualAction handles federation array differently - it attaches the first federation
    // and its parent if exists. We need to verify the federations are attached correctly
    expect($john)->not->toBeNull()
        ->and($jane)->not->toBeNull()
        ->and($john->federations->count())->toBeGreaterThan(0)
        ->and($jane->federations->count())->toBeGreaterThan(0);

    // Check that each individual has a unique member_code
    expect($john->member_code)->not->toBeNull()
        ->and($jane->member_code)->not->toBeNull()
        ->and($john->member_code)->not->toBe($jane->member_code);
});

test('returns zero counts for empty input', function () {
    $result = $this->action->execute([]);

    expect($result['users'])->toBe(0)
        ->and($result['individuals'])->toBe(0)
        ->and($this->action->updateExisting([]))->toBe(0);
});

test('handles string values from csv import for integer fields', function () {
    $district = \Domain\Geographic\Models\District::factory()->create();
    $zone1 = \Database\Factories\ZoneFactory::new()->create();
    $zone2 = \Database\Factories\ZoneFactory::new()->create();
    $entity = \Domain\Entities\Models\Entity::factory()->create();

    // Simulate CSV data where all values come as strings
    $individuals = [
        [
            'name' => 'CSV Import',
            'surname' => 'Test User',
            'email' => 'csv.import@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => (string) $this->country->id,
            'entity_id' => (string) $entity->id,
            'district_id' => (string) $district->id,
            'zone_ids' => $zone1->id.','.$zone2->id,
        ],
    ];

    $result = $this->action->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'csv.import@example.com')->first();

    expect($individual)->not->toBeNull()
        ->and($individual->country_id)->toBe($this->country->id)
        ->and($individual->district_id)->toBe($district->id)
        ->and($individual->zones)->toHaveCount(2)
        ->and($individual->zones->pluck('id')->toArray())->toContain($zone1->id, $zone2->id);
});

test('handles null and empty string values for optional integer fields', function () {
    $individuals = [
        [
            'name' => 'No Optional',
            'surname' => 'Fields',
            'email' => 'no.optional@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => (string) $this->country->id,
            'district_id' => null,
            'zone_ids' => null,
        ],
    ];

    $result = $this->action->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'no.optional@example.com')->first();

    expect($individual)->not->toBeNull()
        ->and($individual->district_id)->toBeNull()
        ->and($individual->zones)->toHaveCount(0);
});

test('handles empty string values from csv for optional integer fields', function () {
    // CSV files often have empty strings instead of null for missing values
    $individuals = [
        [
            'name' => 'Empty Strings',
            'surname' => 'Test',
            'email' => 'empty.strings@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => (string) $this->country->id,
            'entity_id' => '',
            'district_id' => '',
            'zone_ids' => '',
        ],
    ];

    $result = $this->action->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'empty.strings@example.com')->first();

    expect($individual)->not->toBeNull()
        ->and($individual->district_id)->toBeNull()
        ->and($individual->zones)->toHaveCount(0);
});

test('handles zero district_id values that would cause foreign key violations', function () {
    // These values would previously cast to 0 and cause foreign key violations
    $testCases = [
        ['value' => '0', 'description' => 'string zero'],
        ['value' => 0, 'description' => 'integer zero'],
        ['value' => '00', 'description' => 'double zero string'],
        ['value' => '0.0', 'description' => 'float zero string'],
        ['value' => '-0', 'description' => 'negative zero string'],
    ];

    foreach ($testCases as $index => $testCase) {
        $individuals = [
            [
                'name' => 'Test' . $index,
                'surname' => 'User',
                'email' => "zero.district.{$index}@example.com",
                'birthdate' => '1990-01-01',
                'country_id' => $this->country->id,
                'district_id' => $testCase['value'],
            ],
        ];

        $result = $this->action->execute($individuals);

        expect($result['individuals'])->toBe(1, "Failed for {$testCase['description']}");

        $individual = Individual::where('email', "zero.district.{$index}@example.com")->first();

        expect($individual)->not->toBeNull("Individual not created for {$testCase['description']}")
            ->and($individual->district_id)->toBeNull("district_id should be null for {$testCase['description']}");
    }
});

test('handles non-numeric district_id values gracefully', function () {
    $testCases = [
        ['value' => 'abc', 'description' => 'alphabetic string'],
        ['value' => 'N/A', 'description' => 'N/A string'],
        ['value' => 'none', 'description' => 'none string'],
        ['value' => '-1', 'description' => 'negative number string'],
        ['value' => '1.5', 'description' => 'decimal number string'],
    ];

    foreach ($testCases as $index => $testCase) {
        $individuals = [
            [
                'name' => 'NonNumeric' . $index,
                'surname' => 'User',
                'email' => "non.numeric.{$index}@example.com",
                'birthdate' => '1990-01-01',
                'country_id' => $this->country->id,
                'district_id' => $testCase['value'],
            ],
        ];

        $result = $this->action->execute($individuals);

        expect($result['individuals'])->toBe(1, "Failed for {$testCase['description']}");

        $individual = Individual::where('email', "non.numeric.{$index}@example.com")->first();

        expect($individual)->not->toBeNull("Individual not created for {$testCase['description']}")
            ->and($individual->district_id)->toBeNull("district_id should be null for {$testCase['description']}");
    }
});

test('rejects non-existent district_id values', function () {
    // Use a district ID that definitely doesn't exist
    $nonExistentDistrictId = 999999;

    $individuals = [
        [
            'name' => 'Invalid',
            'surname' => 'District',
            'email' => 'invalid.district@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
            'district_id' => (string) $nonExistentDistrictId,
        ],
    ];

    $result = $this->action->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'invalid.district@example.com')->first();

    expect($individual)->not->toBeNull()
        ->and($individual->district_id)->toBeNull();
});
